<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/DbHelper.class.php');

//Read input
$sCommand = $argv[1];
$aCommandArgs = $argv;

//Create DbHelper object
$oDbHelper = new DbHelper();

try {
    if ($sCommand == "list") {                  //List all the pools for the current competition
        listPools();
    } else if ($sCommand == "add") {            //Add the pools from the given battleconfiguration json file
        $sFilename = $argv[2];
        if (!file_exists($sFilename)) {
            throw new Exception("File does not exists");
        }
        addPoolsFromBattleConfiguration($sFilename, true);
    } else {
        echo("Usage:    " . $argv[0] . " <command> <args>\n");
        echo("      Valid commands: list, add <battleconfigurationfile>\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
}

/**
 * List all pools in the current competition
 */
function listPools() {
    global $oDbHelper;

    $sQuery = "SELECT * FROM pool WHERE competition_id = " . COMPETITION_ID;
    $oResult = $oDbHelper->executeQuery($sQuery);
    $oDbHelper->printDbResult($oResult);
}

/**
 * Add all pools from the battleconfiguration file to the database. Ask the user for the
 * name and description of the pool
 *
 * @param $sFilename The battleconfiguration file
 */
function addPoolsFromBattleConfiguration($sFilename) {
    global $oDbHelper;

    //Read the JSON file
    $sContents = file_get_contents($sFilename);
    $sContents = utf8_encode($sContents);
    $oJson = json_decode($sContents);
    if ($oJson == null) {
        throw new Exception("The file seems to be not a valid JSON file");
    }

    //Loop through all teams
    foreach ($oJson->teams as $oTeam) {
        $sPoolId = $oTeam->pool;

        $sQuery = "SELECT * FROM pool WHERE id='" . $sPoolId . "';";
        $oResult = $oDbHelper->executeQuery($sQuery);
        if ($oResult->num_rows > 0) {
            echo "  Skipping pool creation for '" . $sPoolId . "', it is already added.\n";
        } else {
            echo "  New pool detected with id '" . $sPoolId . "'. Please fill in some details: \n";
            echo "    What name should I use for the pool? ";
            $sName = trim(fgets(STDIN));
            echo "    What description should I use for the pool? ";
            $sDescription = trim(fgets(STDIN));

            $sQuery = "INSERT INTO pool (id, competition_id, name, description) VALUES ('" . $sPoolId . "', '" . COMPETITION_ID. "', '" . $oDbHelper->escape($sName). "', '" . $oDbHelper->escape($sDescription) . "')";
            $oResult = $oDbHelper->executeQuery($sQuery);
        }

        echo "  Adding team to the pool\n";
        $sQuery = "INSERT INTO poolteams (competition_id, pool_id, team_id) VALUES ('" . COMPETITION_ID . "', '" . $sPoolId . "', '" . $oTeam->id . "');";
        $oResult = $oDbHelper->executeQuery($sQuery);
    }
}
?>