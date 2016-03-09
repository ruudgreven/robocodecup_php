<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/DbHelper.class.php');

//Read input
$sCommand = $argv[1];
$aCommandArgs = $argv;

//Create DbHelper object
$oDbHelper = new DbHelper();

try {
    if ($sCommand == "list") {                  //List all the teams for the current competition
        listCompetitions();
    } else if ($sCommand == "add") {            //Add the teams from the given battleconfiguration json file
        addCompetition();
    } else {
        echo("Usage:    " . $argv[0] . " <command>\n");
        echo("      Valid commands: list, add\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
}

/**
 * List all competitions in the database
 */
function listCompetitions() {
    global $oDbHelper;

    $sQuery = "SELECT * FROM competition;";
    $oResult = $oDbHelper->executeQuery($sQuery);
    $oDbHelper->printDbResult($oResult);
    echo "Current Competition: " . COMPETITION_ID;
}

/**
 * Add a competition to the file
 */
function addCompetition() {
    global $oDbHelper;

    echo "    What name should I use for the competition? ";
    $sName = trim(fgets(STDIN));

    $sQuery = "INSERT INTO competition (name) VALUES ('" . $oDbHelper->escape($sName) . "')";
    $oResult = $oDbHelper->executeQuery($sQuery);

    echo "\nPlease update your configuration if you want this competition to be the current competition\n";
}
?>