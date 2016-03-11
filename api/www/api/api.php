<?php
require_once(dirname(__FILE__) . '/../../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../../common/classes/DbHelper.class.php');
require_once(dirname(__FILE__) . "/../../../common/inc/flight/flight/Flight.php");

$oDbHelper = new DbHelper();

/**
 * Get the current competition
 */
Flight::route('GET /competition.json', function(){
    global $oDbHelper;

    $sQuery = "SELECT * FROM competition WHERE id=" . COMPETITION_ID . ";";
    $oResult = $oDbHelper->executeQuery($sQuery);
    $oDbHelper->outputDbResult($oResult);
});



/**
 * Get all pools
 */
Flight::route('GET /pool.json', function(){
    global $oDbHelper;

    $sQuery = "SELECT team.id, pool.id AS pool_id, pool.name AS pool_name, pool.description AS pool_description, team.fullname, team.name, team.authorname, team.description FROM pool, team, poolteams WHERE pool.competition_id = team.competition_id = poolteams.competition_id = " . COMPETITION_ID . " AND pool.id = poolteams.pool_id AND team.id = poolteams.team_id;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Cluster by pool id
    $aPools = array();
    while($aObject = $oResult->fetch_assoc()) {
        $sPoolId = $aObject['pool_id'];
        if (!array_key_exists($sPoolId, $aPools)) {
            $aPools[$sPoolId] = (object)[
                'id' => $sPoolId,
                'name' => $aObject['pool_name'],
                'description' => $aObject['pool_description'],
                'teams' => array()
            ];
        }
        array_push($aPools[$sPoolId]->teams, $aObject);
    }

    //Return
    $oDbHelper->outputArray(array_values($aPools));
});



/**
 * Get the details from a team
 */
Flight::route('GET /team/@sTeamId.json', function($sTeamId){
    global $oDbHelper;

    $sQuery = "SELECT id, fullname, name, authorname, description FROM team WHERE competition_id = " . COMPETITION_ID . " AND id='" . $sTeamId . "'";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Return
    $oDbHelper->outputDbResult($oResult);
});



/**
 * Get all rounds and the previous, current and next round. Previous and next are -1 when there is no previous or next.
 */
Flight::route('GET /round.json', function(){
    global $oDbHelper;


    //Get all rounds
    $sQuery = "SELECT number, startdate, enddate FROM round WHERE competition_id=" . COMPETITION_ID . " ORDER BY number;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Create round object
    $oRounds = (object)[
        'previous' => -1,
        'current' => -1,
        'next' => -1,
        'rounds' => array()
    ];

    //Determine dates
    $dTimeZone = new DateTimeZone(TIMEZONE);
    $dToday = new DateTime("now", $dTimeZone);

    $iLowestRound = 0;
    $iHighestRound = 0;
    while($aObject = $oResult->fetch_assoc()) {
        array_push($oRounds->rounds, $aObject);

        //Find dates
        $dStartdate = DateTime::createFromFormat('Y-m-d', $aObject['startdate'], $dTimeZone);
        $dEnddate = DateTime::createFromFormat('Y-m-d', $aObject['enddate'], $dTimeZone);
        if ($dToday >= $dStartdate && $dToday <= $dEnddate) {
            $oRounds->current = intval($aObject['number']);
        }

        //Set highest and lowest
        if ($iLowestRound == 0) {
            $iLowestRound = intval($aObject['number']);
        }
        $iHighestRound = intval($aObject['number']);
    }

    //Check previous and next round
    if ($oRounds->current > $iLowestRound) {
        $oRounds->previous = $oRounds->current - 1;
    }
    if ($oRounds->current < ($iHighestRound - 1)) {
        $oRounds->next = $oRounds->current + 1;
    }

    //Return
    $oDbHelper->outputArray($oRounds);
});



/**
 * Get all teams that come out in a certain round
 */
Flight::route('GET /round/@sRoundId/team.json', function($sRoundId){
    global $oDbHelper;

    if (!preg_match('/^[0-9]+$/', $sRoundId)) {
        $oDbHelper->outputError("The round should be numeric");
        return;
    }

    //Get all rounds
    $sQuery = "SELECT team.id , pool.id AS pool_id, pool.name AS pool_name, team.fullname, team.name, team.authorname, team.description FROM battle, battlescore, team, pool WHERE battle.competition_id = team.competition_id = battlescore.competition_id = pool.competition_id = " . COMPETITION_ID . " AND battlescore.battle_id = battle.id AND battlescore.team_id = team.id AND battlescore.pool_id = pool.id AND round_number=" . $sRoundId. " GROUP BY id ORDER BY name;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Cluster by pool id
    $aPools = array();
    while($aObject = $oResult->fetch_assoc()) {
        $sPoolId = $aObject['pool_id'];
        if (!array_key_exists($sPoolId, $aPools)) {
            $aPools[$sPoolId] = (object)[
                'id' => $sPoolId,
                'name' => $aObject['pool_name'],
                'teams' => array()
            ];
        }
        array_push($aPools[$sPoolId]->teams, $aObject);
    }

    //Return
    $oDbHelper->outputArray(array_values($aPools));
});



/**
 * Get the ranking of this round. All scores summed up by team
 */
Flight::route('GET /round/@sRoundId/ranking.json', function($sRoundId){
    global $oDbHelper;

    if (!preg_match('/^[0-9]+$/', $sRoundId)) {
        $oDbHelper->outputError("The round should be numeric");
        return;
    }
    //Get all rounds
    $sQuery = "SELECT pool.id AS pool_id, pool.name AS pool_name, team.id as team_id, team.name AS team_name, ROUND(AVG(battlescore.totalscore)) AS totalscore, ROUND(AVG(battlescore.survivalscore)) AS survivalscore, ROUND(AVG(battlescore.survivalbonus)) AS survivalbonus, ROUND(AVG(battlescore.bulletdamage)) AS bulletdamage, ROUND(AVG(battlescore.bulletbonus)) AS bulletbonus, ROUND(AVG(battlescore.ramdamage)) AS ramdamage, ROUND(AVG(battlescore.rambonus)) AS rambonus, ROUND(AVG(battlescore.firsts)) AS firsts, ROUND(AVG(battlescore.seconds)) AS seconds, ROUND(AVG(battlescore.thirds)) AS thirds, COUNT(*) AS totalbattles FROM battle, battlescore, team, pool WHERE battle.competition_id = team.competition_id = battlescore.competition_id = pool.competition_id = " . COMPETITION_ID . " AND battlescore.battle_id = battle.id AND battlescore.team_id = team.id AND battlescore.pool_id = pool.id AND round_number=" . $sRoundId. " AND battle.official = 1 GROUP BY team_id, pool_id ORDER BY AVG(battlescore.totalscore) DESC;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Cluster by pool id
    $aScores = array();
    while($aObject = $oResult->fetch_assoc()) {
        array_push($aScores, $aObject);
    }

    //Return
    $oDbHelper->outputArray($aScores);
});



/**
 * Get all battles for the given team in this round
 */
Flight::route('GET /round/@sRoundId/@sTeamId/battles.json', function($sRoundId, $sTeamId){
    global $oDbHelper;

    if (!preg_match('/^[0-9]+$/', $sRoundId)) {
        $oDbHelper->outputError("The round should be numeric");
        return;
    }
    //Get all rounds
    $sQuery =
        "SELECT battle.datetime, battle.replay_file, battle.results_file, pool.name AS pool_name, team.name AS team_name, battlescore.* FROM battlescore, battle, team, pool WHERE battle_id IN " .
          "(SELECT battle_id FROM battle, battlescore" .
            " WHERE battle.id = battlescore.battle_id AND " .
            "battle.competition_id = battlescore.competition_id = " . COMPETITION_ID . " AND ".
            "round_number=" . $sRoundId . " AND ".
            "team_id='" . $sTeamId . "' )" .
        " AND battlescore.battle_id = battle.id AND " .
        " battlescore.team_id = team.id AND " .
        " battlescore.pool_id = pool.id " .
        " ORDER BY battle_id, totalscore DESC;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Cluster by battle id
    $aBattles = array();
    while($aObject = $oResult->fetch_assoc()) {
        if (!array_key_exists($aObject['battle_id'], $aBattles)) {
            $aBattles[$aObject['battle_id']] = (object) [
                'id' => $aObject['battle_id'],
                'datetime' => $aObject['datetime'],
                'pool_id' => $aObject['pool_id'],
                'pool_name' => $aObject['pool_name'],
                'replay_url' => DOWNLOADS_URL . "/round" . $sRoundId . "/" . $aObject['replay_file'],
                'results_url' => DOWNLOADS_URL . "/round" . $sRoundId . "/" . $aObject['results_file'],
                'scores' => array()
            ];
        }

        $oScore = (object) [
            'team_id' => $aObject['team_id'],
            'team_name' => $aObject['team_name'],
            'totalscore' => $aObject['totalscore'],
            'totalscore' => $aObject['totalscore'],
            'totalpercentage' => $aObject['totalpercentage'],
            'survivalscore' => $aObject['survivalscore'],
            'survivalbonus' => $aObject['survivalbonus'],
            'bulletdamage' => $aObject['bulletdamage'],
            'bulletbonus' => $aObject['bulletbonus'],
            'ramdamage' => $aObject['ramdamage'],
            'rambonus' => $aObject['rambonus'],
            'firsts' => $aObject['firsts'],
            'seconds' => $aObject['seconds'],
            'thirds' => $aObject['thirds']
        ];
        array_push($aBattles[$aObject['battle_id']]->scores, $oScore);
    }

    //Remove array keys
    $aBattles = array_values($aBattles);

    //Return
    $oDbHelper->outputArray($aBattles);
});



/**
 * Get the featured messages
 */
Flight::route('GET /messages/featured.json', function(){
    global $oDbHelper;

    $sQuery = "SELECT * FROM message WHERE competition_id = " . COMPETITION_ID . " AND CURDATE() >= featuredfrom  AND CURDATE() <= featuredtill;";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Return
    $oDbHelper->outputDbResult($oResult);
});

/**
 * Get the featured messages
 */
Flight::route('GET /messages.json', function(){
    global $oDbHelper;

    $sQuery = "SELECT * FROM message WHERE competition_id = " . COMPETITION_ID . " AND CURDATE() >= showfrom AND (CURDATE() <= showtill OR showtill = '0000-00-00 00:00');";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Return
    $oDbHelper->outputDbResult($oResult);
});

Flight::start();
?>
