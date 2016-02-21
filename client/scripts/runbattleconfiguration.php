<?php
include_once(dirname(__FILE__) . '/../config.inc.php');
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

//Initialize pools
$aPools = array();

//Read all the team entries and extract the JAR file associated with it and add it to the correct pool
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

print_r($aPools);
