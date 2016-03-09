<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/DbHelper.class.php');

//Read input
$sCommand = $argv[1];
$aCommandArgs = $argv;

//Create DbHelper object
$oDbHelper = new DbHelper();

try {
    if ($sCommand == "add") {            //Add a message
        addMessage();
    } else {
        echo("Usage:    " . $argv[0] . " add\n");
        echo("      Valid commands: add\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
    die();
}

/**
 * Add all played battles from the given folder
 */
function addMessage() {
    global $oDbHelper;

    echo "What is the title of the messages?\n";
    $sTitle = askString();

    echo "What is the message?\n";
    $sMessage = askString();

    echo "Is there an action (link) associated with the message?\n";
    $sYesNo = askString();
    $sActionTitle = null;
    $sActionMessage = null;
    if ($sYesNo == "yes") {
        echo "What is the action title?\n";
        $sActionTitle = askString();
        echo "What is the action link?\n";
        $sActionLink = askString();
    }

    echo "What is the url for the image to show? (press enter for no image)\n";
    $sImageUrl = askString(true);

    echo "What is the startdate (DD-MM-YYYY) of the message (Visible from)? (enter for today)\n";
    $sBeginDate = askDate(true);

    echo "What is the enddate (DD-MM-YYYY) of the message (Visible until)? (enter for none)\n";
    $sEndDate = askDate(false, true);

    echo "What is the startdate (DD-MM-YYYY) of the featured time of the message? (E.g., visible on homepage, enter for today)\n";
    $sFeaturedBeginDate = askDate(true);

    echo "What is the enddate (DD-MM-YYYY) of the featured time of the message?\n";
    $sFeaturedEndDate = askDate();

    $dTimeZone = new DateTimeZone(TIMEZONE);
    $dCurDate = new DateTime("now", $dTimeZone);

    $sQuery = "INSERT INTO message (competition_id, title, message, actiontitle, actionlink, imageurl, date, showfrom, showtill, featuredfrom, featuredtill) VALUES (" .
        COMPETITION_ID . ", ".
        "'" . $oDbHelper->escape($sTitle) . "'," .
        "'" . $oDbHelper->escape($sMessage) . "'," .
        "'" . $oDbHelper->escape($sActionTitle) . "'," .
        "'" . $oDbHelper->escape($sActionLink) . "'," .
        "'" . $oDbHelper->escape($sImageUrl) . "'," .
        "'" . $dCurDate->format('Y-m-d') . "'," .
        "'" .$sBeginDate . "'," .
        "'" .$sEndDate . "'," .
        "'" .$sFeaturedBeginDate . "'," .
        "'" .$sFeaturedEndDate . "'" .
        ");";

    echo $sQuery;
    $oResult = $oDbHelper->executeQuery($sQuery);

    echo "\nDone!\n";
}

function askDate($bEnterForToday = false, $bEnterForNone = false) {
    $dTimeZone = new DateTimeZone(TIMEZONE);

    $dRunDateTime = null;
    while ($dRunDateTime == null) {
        try {
            $sDate = trim(fgets(STDIN));
            if ($sDate=="" && $bEnterForToday) {
                $dRunDateTime = new DateTime("now", $dTimeZone);
            } else if ($sDate == "" && $bEnterForNone) {
                return "null";
            } else {
                $dRunDateTime = DateTime::createFromFormat('d-m-Y', $sDate, $dTimeZone);
            }
        } catch (Exception $e) {
            echo "\nPlease enter a date in the format DD-MM-YYYY\n";
        }
    }

    return $dRunDateTime->format('Y-m-d');
}

function askString($bEmptyAllowed = false) {
    $sString = null;
    while ($sString == null) {
        $sInput = trim(fgets(STDIN));
        if ($bEmptyAllowed) {
            $sString = $sInput;
            return $sString;
        } else {
            if ($sInput != "") {
                $sString = $sInput;
            }
        }
    }

    return $sString;
}
?>