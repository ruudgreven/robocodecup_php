<?php
include_once(dirname(__FILE__) . '/../../common/config.inc.php');
/**
 * Runs a battle configuration file
 */

//Read input
$sBattleconfigurationFilename = $argv[1];
$sOutputFolder = $argv[2];

//Do some checks on the parameters
if ($sBattleconfigurationFilename == "" || $sOutputFolder == "") {
    echo("Usage:   " . $argv[0] . " <battleconfiguration file> <tmp and outputfolder>\n");
    echo("Example: " . $argv[0] . " <battleconfiguration file> ~/tmp\n");
    exit;
}

if (!is_file($sBattleconfigurationFilename)) {
    echo("ERROR: The battleconfiguration file '" . $sBattleconfigurationFilename . "' does not exists, or is not a file\n");
    exit();
}
if (file_exists($sOutputFolder)) {
    echo("ERROR: File or directory '" . $sOutputFolder . "' already exists. Point to a new folder!\n");
    exit();
}
if (!mkdir($sOutputFolder)) {
    echo("ERROR: Can't create directory '" . $sOutputFolder . "'\n");
    exit();
}

//Read the JSON file
$sContents = file_get_contents($sBattleconfigurationFilename);
$sContents = utf8_encode($sContents);
$oJson = json_decode($sContents);
if ($oJson == null) {
    echo("ERROR: The JSON file '" . $sBattleconfigurationFilename . "' seems not valid");
    exit();
}


//Read all the team entries and extract the JAR file associated with it and add it to the correct pool
$aPools = array();
$sJarFolder = $oJson->folder;
echo "Processing files in folder '" . $sJarFolder . "'\n";
if ($hFileHandle = opendir($sJarFolder)) {

    //Read all teams from the json file and open and extract the jar file
    foreach ($oJson->teams as $oTeam) {
        echo "  Adding team with id '" . $oTeam->id . "'\n";

        echo "    Extracting '" . $oTeam->filename ."'...";

        //Extract the jar file to the outputfolder
        $oZip = new ZipArchive;
        if ($oZip->open($sJarFolder . "/" . $oTeam->filename) === TRUE) {
            $oZip->extractTo($sOutputFolder . "/teams");
            $oZip->close();
            echo("OK!\n");

            //Create a pool for this team when it does not exists
            if (!array_key_exists($oTeam->pool, $aPools)) {
                $aPools[$oTeam->pool] = array();
            }

            //Add the team to the pool
            array_push($aPools[$oTeam->pool], $oTeam);
        } else {
            echo("ERROR: File cannot be extracted. Skipped!\n");
        }
    }
} else {
    echo("ERROR: Directory " . $sJarFolder . " not found\n");
    exit();
}



//Generate battles for every pool
$aPoolBattles = array();
foreach ($aPools as $sPoolname => $aPool) {
    $aPoolBattles[$sPoolname] = array();

    foreach ($aPool as $oTeam) {
        //Read the teamfile and create the correct format (with dots and without .team)
        $sTeamfile = str_replace("/", ".", $oTeam->teamfile);
        $sTeamfile = substr($sTeamfile, 0, -5);

        //Add a match to all other teams in this pool
        foreach ($aPool as $oTeam2) {
            if ($oTeam->id != $oTeam2->id) {
                //Read the teamfile of the other team and create the correct format (with dots and without .team)
                $sTeamfile2 = str_replace("/", ".", $oTeam2->teamfile);
                $sTeamfile2 = substr($sTeamfile2, 0, -5);

                //Create a battle
                $oBattle = (object) [
                    'team1_teamfile' => $sTeamfile,
                    'team1_id' => $oTeam->id,
                    'team2_teamfile' => $sTeamfile2,
                    'team2_id' => $oTeam2->id,
                ];

                //Add the battle
                array_push($aPoolBattles[$sPoolname], $oBattle);
            }
        }
    }
}



//****** Show scheduled battles ******
echo "\nThe following battles will be played:\n";
foreach ($aPoolBattles as $sPoolname => $aPoolBattle) {
    echo("        In pool '" . $sPoolname . "':\n");
    foreach ($aPoolBattle as $oBattle) {
        echo("          " . $oBattle->team1_id . " - " . $oBattle->team2_id . "\n");
    }
}

//Ask confirmation for battles
$bProceed = false;
while (!$bProceed) {
    echo("\nAre you sure these are the correct battles? [y/n]\n");
    $sAnswer = trim(fgets(STDIN));

    if ($sAnswer == "y" || $sAnswer == "Y") {
        $bProceed = true;
    } else if ($sAnswer == "n" || $sAnswer == "N") {
        echo("OK, bye!\n");
        exit();
    }
}



//****** Generating the battles ******
echo "Generating battles...\n";

//Create a folder to place the battles
mkdir($sOutputFolder . "/battles");

foreach ($aPoolBattles as $sPoolname => $aPoolBattle) {
    foreach ($aPoolBattle as $oBattle) {
        echo "  Generating " . $sPoolname . ": " . $oBattle->team1_id . " - " . $oBattle->team2_id . "\n";

        //Configure filenames
        $sTemplateFilenameStart = "../" . TEMPLATE_FOLDER . "/";
        $sFilenameStart = $sOutputFolder . "/battles/" . $sPoolname . "_" . $oBattle->team1_id . "-" . $oBattle->team2_id;
        $aTeams = [$oBattle->team1_teamfile, $oBattle->team2_teamfile];

        //Build battle files based upon the templates
        generateBattleFile($sTemplateFilenameStart . "singleround.battle", $sFilenameStart . "_singleround.battle", $aTeams);
        generateBattleFile($sTemplateFilenameStart . "tenrounds.battle", $sFilenameStart . "_tenrounds.battle", $aTeams);
    }
}



//Ask confirmation if the correct files are generated
$bProceed = false;
while (!$bProceed) {
    echo("\nAre you sure all battlefiles are created and you want to run them? [y/n]\n");
    $sAnswer = trim(fgets(STDIN));

    if ($sAnswer == "y" || $sAnswer == "Y") {
        $bProceed = true;
    } else if ($sAnswer == "n" || $sAnswer == "N") {
        echo("OK, bye!\n");
        exit();
    }
}



//****** Run the generated battlefiles ******
$iNumberOfBattlesRunned = 0;
echo "Running the battles (This may take some time. Capturing output to file)...";
ob_start();

//Create a folder to place the battles
mkdir($sOutputFolder . "/output");

if ($hFileHandle = opendir($sOutputFolder . "/battles")) {
    while (false !== ($sFilename = readdir($hFileHandle))) {
        if ($sFilename != "." && $sFilename != "..") {
            if (strrpos($sFilename, ".battle")) {
                if (strpos($sFilename, "_tenrounds")) {
                    $iNumberOfBattlesRunned++;
                    runBattle($sOutputFolder . "/teams", $sOutputFolder . "/battles", $sFilename, $sOutputFolder . "/output");
                }
            }
        }
    }
    closedir($hFileHandle);
    echo "DONE!\n";
}
$sOutputString = ob_get_contents();
ob_end_clean();
echo "OK!\n";
file_put_contents($sOutputFolder . "/log.txt", $sOutputString);



//****** Copy battle configuration file to outputfolder ******
copy($sBattleconfigurationFilename, $sOutputFolder . "/battleconfiguration.json");



//****** Check number of output files ******
echo "Checking number of outputfiles...";
$iCountedBattleFiles = 0;
if ($hFileHandle = opendir($sOutputFolder . "/output")) {
    while (false !== ($sFilename = readdir($hFileHandle))) {
        if ($sFilename!="." && $sFilename!="..") {
            $iCountedBattleFiles++;
        }
    }
}

// The number of outputfiles should be 2 x $iNumberOfBattlesRunned
if ($iCountedBattleFiles != $iNumberOfBattlesRunned * 2) {
    echo "ERROR! (Expected: " . ($iNumberOfBattlesRunned * 2) . ", Found: "  . $iCountedBattleFiles . ") , Please check log.txt in " . $sOutputFolder . "\n";
} else {
    echo "OK!\n";
}



/**
 * Generate a battle file
 * sTemplate the template file
 * sFilename the output filename
 * $sTeams the teams in the battle
 */
function generateBattleFile($sTemplate, $sFilename, $aTeams) {
    $fFile = fopen($sFilename, "w") or die("Unable to open file!");

    //Read template and write back
    $fTemplate = fopen($sTemplate, "r");
    $sContents = fread($fTemplate, filesize($sTemplate));
    fclose($fTemplate);
    fwrite($fFile, $sContents);
    //Write battles
    $sBattleString = "robocode.battle.selectedRobots=";
    foreach ($aTeams as $sTeamname) {
        $sBattleString .= $sTeamname . "*,";
    }
    fwrite($fFile, $sBattleString . "\n");
    fclose($fFile);
}

/**
 * Run a battle
 * @param $sRobotFolder
 * @param $sBattleFolder
 * @param $sBattleFilename
 * @param $sOutputFolder
 */
function runBattle($sRobotFolder, $sBattleFolder, $sBattleFilename, $sOutputFolder) {
    echo "  Running battle " . $sBattleFilename . "\n";
    $sResultFilename = str_replace(".battle", ".results", $sBattleFilename);
    $sReplayFilename = str_replace(".battle", ".br", $sBattleFilename);
    $sCommand = "java -Xmx512M -Dsun.io.useCanonCaches=false -DROBOTPATH=" . $sRobotFolder . " -cp " . ROBOCODE_PATH . "libs/robocode.jar:" . ROBOCODE_PATH . "libs/robocode.ui-1.9.2.5.jar robocode.Robocode -battle " . $sBattleFolder . "/" . $sBattleFilename . " -nodisplay -results " . $sOutputFolder . "/" . $sResultFilename . " -record " . $sOutputFolder . "/" . $sReplayFilename;
    echo "    " . $sCommand . "\n";
    exec($sCommand);
}

