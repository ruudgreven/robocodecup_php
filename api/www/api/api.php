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

    $sQuery = "SELECT team.id, pool.id AS pool_id, pool.name AS pool_name, team.fullname, team.name, team.authorname, team.description FROM pool, team, poolteams WHERE pool.competition_id = team.competition_id = poolteams.competition_id = " . COMPETITION_ID . " AND pool.id = poolteams.pool_id AND team.id = poolteams.team_id;";
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
    $oDbHelper->outputArray($aPools);
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
    $oDbHelper->outputArray($aPools);
});




/**
 * Get all the battles played in this round
 */
Flight::route('GET /round/@sRoundId/battles.json', function($sRoundId){
    global $oDbHelper;

    if (!preg_match('/^[0-9]+$/', $sRoundId)) {
        $oDbHelper->outputError("The round should be numeric");
        return;
    }

    //Get all rounds
    $sQuery = "SELECT battle.id AS battle_id, pool.id AS pool_id, pool.name AS pool_name, battle.datetime AS battle_datetime, battle.official AS battle_official, team.id as team_id, team.name AS team_name, battlescore.* FROM battle, battlescore, team, pool WHERE battle.competition_id = team.competition_id = battlescore.competition_id = pool.competition_id = " . COMPETITION_ID . " AND battlescore.battle_id = battle.id AND battlescore.team_id = team.id AND battlescore.pool_id = pool.id AND round_number=" . $sRoundId. ";";
    $oResult = $oDbHelper->executeQuery($sQuery);

    //Cluster by pool id
    $aPools = array();
    while($aObject = $oResult->fetch_assoc()) {

        //Create pool object
        $sPoolId = $aObject['pool_id'];
        $sBattleId = $aObject['battle_id'];
        if (!array_key_exists($sPoolId, $aPools)) {
            $aPools[$sPoolId] = (object)[
                'id' => $sPoolId,
                'name' => $aObject['pool_name'],
                'battles' => array()
            ];
        }

        //Create battle object inside pool
        if (!array_key_exists($sBattleId, $aPools[$sPoolId]->battles)) {
            $aPools[$sPoolId]->battles[$sBattleId] = (object)[
                'id' => $aObject['battle_id'],
                'datetime' => $aObject['battle_datetime'],
                'official' => $aObject['battle_official'],
                'scores' => array()
            ];
        }

        //Create score object
        $oScore = (object) [
            'id' => $aObject['team_id'],
            'name' => $aObject['team_name'],
            'rank' => $aObject['rank'],
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

        array_push($aPools[$sPoolId]->battles[$sBattleId]->scores, $oScore);
    }

    //Return
    $oDbHelper->outputArray($aPools);
});

Flight::start();
?>
