<?php
include_once(dirname(__FILE__) . '/../../common/config.inc.php');
include_once(dirname(__FILE__) . '/battleselectors/BaseBattleSelector.class.php');
/**
 * Runs a battle configuration file
 */

//Read input
$sBattleconfigurationFilename = $argv[1];
$sOutputFolder = $argv[2];
$sSelector = $argv[3];

//Do some checks on the parameters
if ($sBattleconfigurationFilename == "" || $sOutputFolder == "" || $sSelector == "") {
    echo("Usage:   " . $argv[0] . " <battleconfiguration file> <tmp and outputfolder> <selector>\n");
    echo("Example: " . $argv[0] . " <battleconfiguration file> ~/tmp battleselectors/one_to_one_per_pool.class.php\n");
    exit;
}

if (!is_file($sBattleconfigurationFilename)) {
    echo("ERROR: The battleconfiguration file '" . $sBattleconfigurationFilename . "' does not exists, or is not a file\n");
    exit();
}
if (!is_file($sSelector)) {
    echo("ERROR: The selector class file '" . $sSelector . "' does not exists, or is not a file\n");
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

//****** Run battle selector
require_once(dirname(__FILE__) . "/" . $sSelector);
$bs = new BattleSelector($sOutputFolder);

//Generate battles
$bs->generateBattles($aPools);

//Show the scheduled battles
$bs->showBattles();


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

//Write battles to json file for later reference
$bs->writeRunnedBattles();


//****** Generating the battles ******
echo "Generating battles...\n";

//Create a folder to place the battles
mkdir($sOutputFolder . "/battles");

//Generate battle files
$bs->writeBattleFiles();


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
echo "Running the battles (This may take some time. Capturing output to file). If you see some errors, cancel the progress and check the teams and restart";
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

?>