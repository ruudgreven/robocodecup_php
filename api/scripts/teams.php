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
        listTeams();
    } else if ($sCommand == "add") {            //Add the teams from the given battleconfiguration json file
        $sFilename = $argv[2];
        if (!file_exists($sFilename)) {
            throw new Exception("File does not exists");
        }
        addTeamsFromBattleConfiguration($sFilename, true);
    } else if ($sCommand == "setlogo") {
        $sTeamname = $argv[2];
        $sLogoUrl = $argv[3];

        setTeamLogo($sTeamname, $sLogoUrl, false);
    } else if ($sCommand == "setlogothumb") {
        $sTeamname = $argv[2];
        $sLogoUrl = $argv[3];

        setTeamLogo($sTeamname, $sLogoUrl, true);
    } else {
        echo("Usage:    " . $argv[0] . " <command> <args>\n");
        echo("      Valid commands: list, add <battleconfigurationfile>, setlogo <team_id> <logourl>, setlogothumb <team_id> <logourl>\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
}

/**
 * List all teams in the current competition
 */
function listTeams() {
    global $oDbHelper;

    $sQuery = "SELECT * FROM team WHERE competition_id = " . COMPETITION_ID;
    $oResult = $oDbHelper->executeQuery($sQuery);
    $oDbHelper->printDbResult($oResult);
}

/**
 * Add all teams from the battleconfiguration file to the database
 *
 * @param $sFilename The battleconfiguration file
 * @param $bUpdate Update a team or ignore a team that already exists
 */
function addTeamsFromBattleConfiguration($sFilename, $bUpdate = true) {
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
        if (!$bUpdate) {
            $sQuery = "INSERT INTO team (id, competition_id, fullname, name, authorname, description) VALUES (" .
                "'" . $oTeam->id . "'," .
                "'" . COMPETITION_ID . "'," .
                "'" . substr(str_replace("/", ".", $oTeam->teamfile), 0, -5) . "'," .
                "'" . $oDbHelper->escape($oTeam->teamname) . "'," .
                "'" . $oDbHelper->escape($oTeam->authorname) . "'," .
                "'" . $oDbHelper->escape($oTeam->description) ."'" .
                ");";
        } else {
            $sQuery = "INSERT INTO team (id, competition_id, fullname, name, authorname, description) VALUES (" .
                "'" . $oTeam->id . "'," .
                "'" . COMPETITION_ID . "'," .
                "'" . substr(str_replace("/", ".", $oTeam->teamfile), 0, -5) . "'," .
                "'" . $oDbHelper->escape($oTeam->teamname) . "'," .
                "'" . $oDbHelper->escape($oTeam->authorname) . "'," .
                "'" . $oDbHelper->escape($oTeam->description) ."'" .
                ") ON DUPLICATE KEY UPDATE fullname = '" . substr(str_replace("/", ".", $oTeam->teamfile), 0, -5) . "', name = '" . $oDbHelper->escape($oTeam->teamname) . "', authorname = '" . $oDbHelper->escape($oTeam->authorname) . "', description = '" . $oDbHelper->escape($oTeam->description) . "';";
        }
        $oResult = $oDbHelper->executeQuery($sQuery);
    }
}

/**
 * Set a team logo
 * @param The teamname
 * @param The logo url
 */
function setTeamLogo($sTeamname, $sLogoUrl, $bThumb) {
    global $oDbHelper;

    //Check if the team exists
    $oResult = $oDbHelper->executeQuery("SELECT * FROM team WHERE id='" . $sTeamname . "';");
    if (mysqli_num_rows($oResult) !== 1) {
        throw new Exception("The team id does not exists");
    }

    //Check if the image file exists
    $aFileHeaders = get_headers($sLogoUrl);
    if($aFileHeaders[0] == 'HTTP/1.1 404 Not Found') {
        throw new Exception("The image file does not exists");
    }

    if (!$bThumb) {
        echo "Setting logo...";
        $sQuery = "UPDATE team SET teamlogourl='" . $sLogoUrl . "' WHERE id='" . $sTeamname . "'; ";
        $oResult = $oDbHelper->executeQuery($sQuery);
    } else {
        echo "Setting thumbnail logo...";
        $sQuery = "UPDATE team SET teamlogourl_thumb='" . $sLogoUrl . "' WHERE id='" . $sTeamname . "'; ";
        $oResult = $oDbHelper->executeQuery($sQuery);
    }
    echo "OK!\n";
}
?>