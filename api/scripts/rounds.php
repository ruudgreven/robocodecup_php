<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/DbHelper.class.php');

//Read input
$sCommand = $argv[1];
$aCommandArgs = $argv;

//Create DbHelper object
$oDbHelper = new DbHelper();

try {
    if ($sCommand == "list") {                  //List all the rounds for the given competition
        listRounds();
    } else if ($sCommand == "add") {            //Add rounds with the given properties
        if (preg_match('/^[0-9]+$/', $argv[2]) || preg_match('/^[0-9]+-[0-9]+$/', $argv[2])) {
            if (preg_match('/^[0-9]+$/', $argv[3])) {
                if (preg_match('/^[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]$/', $argv[4])) {
                    addRound($argv[2], $argv[3], $argv[4]);
                } else {
                    throw new Exception("The startdate argument should has a date that look like 00-00-0000");
                }
            } else {
                throw new Exception("The days argument should be numeric.");
            }
        } else {
            throw new Exception("The period argument should look like <num> or <num>-<num>.");
        }
    } else {
        echo("Usage:    " . $argv[0] . " <command> <args>\n");
        echo("      Valid commands: list, add\n");
        echo("Usage:    " . $argv[0] . " add <period> <number_of_days_per_round> <startdate>\n");
        echo("Example:  " . $argv[0] . " add 3-8 7 07-03-2016");
        echo("          Added rounds 3 to 8, one rounds takes 7 days (a week) and the rounds started on day 07-03-2016\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
}

/**
 * List all rounds in the database
 */
function listRounds() {
    global $oDbHelper;

    $sQuery = "SELECT * FROM round WHERE competition_id = " . COMPETITION_ID . ";";
    $oResult = $oDbHelper->executeQuery($sQuery);
    $oDbHelper->printDbResult($oResult);
}

/**
 * Add a round to the file
 * $sPeriod a period in the format <num> or <num>-<num>. The first one means one round, the second means from first till second.
 * $sDays the number of days a round take
 * $sDate the date the round(s) starts
 */
function addRound($sPeriod, $sDays, $sDate) {
    global $oDbHelper;

    if (preg_match('/^[0-9]+$/', $sPeriod)) {
        $iBeginRound = intval($sPeriod);
        $iNumRounds = 1;
    } else {
        $aParts = explode("-", $sPeriod);
        $iBeginRound = intval(trim($aParts[0]));
        $iNumRounds = intval(trim($aParts[1]) - trim($aParts[0])) + 1;
    }
    $iNumberOfDays = intval($sDays);

    $dTimeZone = new DateTimeZone(TIMEZONE);
    $dStartDate = DateTime::createFromFormat('d-m-Y', $sDate, $dTimeZone);
    $sStartDate = $dStartDate->format('Y-m-d');

    echo "I am going to add " . $iNumRounds . " rounds, starting with round " . $iBeginRound . " at day " . $sStartDate . ", rounds takes " . $iNumberOfDays . " days. Ok?";
    $sInput = trim(fgets(STDIN));

    if ($sInput == "y" || $sInput == "Y") {
        echo "\n    Adding rounds...";
        for ($i = $iBeginRound; $i < $iBeginRound + $iNumRounds; $i++) {
            //Create enddate
            $dEndDate = clone $dStartDate;
            $dEndDate->add(new DateInterval('P' . ($iNumberOfDays - 1) . 'D'));

            $sQuery = "INSERT INTO round (number, competition_id, startdate, enddate) VALUES (" . $i . ", " . COMPETITION_ID . ", '" . $dStartDate->format('Y-m-d') . "', '" . $dEndDate->format('Y-m-d') . "')";
            $oResult = $oDbHelper->executeQuery($sQuery);

            //Add the number of days to startdate
            $dStartDate->add(new DateInterval('P' . $iNumberOfDays . 'D'));
        }
        echo "OK!\n";
        echo "Printing added rounds:\n";

        $sQuery = "SELECT * FROM round WHERE competition_id = " . COMPETITION_ID . " AND number >= " . $iBeginRound . " AND number < " . ($iBeginRound + $iNumRounds) . ";";
        $oResult = $oDbHelper->executeQuery($sQuery);
        $oDbHelper->printDbResult($oResult);
    } else {
        echo "Ok, doing nothing...";
    }
}
?>