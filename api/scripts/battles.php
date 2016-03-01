<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/DbHelper.class.php');

//Read input
$sCommand = $argv[1];
$aCommandArgs = $argv;

//Create DbHelper object
$oDbHelper = new DbHelper();

try {
    if ($sCommand == "add") {            //Add all the files in the given battle folder
        $bOfficial = false;
        if ($argv[2] == "official") {
            $bOfficial = true;
        } else if ($argv[2] == "unofficial") {
            $bOfficial = false;
        } else {
            throw new Exception("Second argument must be 'official' or 'unofficial'");
        }

        if (!preg_match('/^[0-9]+$/', $argv[3])) {
            throw new Exception("The third argument should be a number, the round number");
        }
        $iRound = intval($argv[3]);

        $sFolder = $argv[4];
        if (file_exists($sFolder) && is_dir($sFolder)) {
            addBattles($sFolder, $bOfficial, $iRound);
        } else {
            throw new Exception("The given folder does not exist, or is not a directory");
        }

    } else {
        echo("Usage:    " . $argv[0] . " add <official_or_unofficial> <round> <folder_from_runbattles>\n");
        echo("      Valid commands: add\n");
    }
} catch (Exception $e) {
    $oDbHelper->printError($e->getMessage() ."\n");
    die();
}

/**
 * Add all played battles from the given folder
 */
function addBattles($sFolder, $bOfficial, $iRound) {
    global $oDbHelper;

    $sOutputFolder = $sFolder . "/output";
    $sRunnedBattles = $sFolder . "/runnedbattles.json";
    if (!file_exists($sOutputFolder) || !is_dir($sOutputFolder)) {
        throw new Exception("There is no subfolder named 'output' in '" . $sFolder . "'");
    }
    if (!file_exists($sRunnedBattles) || !is_file($sRunnedBattles)) {
        throw new Exception("There is no file named 'battleconfiguration.json' in '" . $sFolder . "'");
    }

    //Read the JSON file
    $sContents = file_get_contents($sRunnedBattles);
    $sContents = utf8_encode($sContents);
    $oJson = json_decode($sContents);
    if ($oJson == null) {
        echo("\nERROR: The JSON file '" . $sRunnedBattles . "' seems not valid");
        exit();
    }

    //Loop through all the battles in the json file
    echo "Loop through battles in the battleconfiguration...\n";
    $dTimeZone = new DateTimeZone(TIMEZONE);
    foreach ($oJson as $sPoolname => $aBattles) {
        echo "  Pool '" . $sPoolname . "'...\n";
        foreach($aBattles as $oBattle) {
            $sResultsFilename = $sFolder . "/" . $oBattle->filename_results_tenrounds;
            if (file_exists($sResultsFilename)) {
                $iDatetime = filectime($sResultsFilename);

                //Get creation date of file and use as the rundatetime of the battle
                $dRunDateTime = DateTime::createFromFormat('U', $iDatetime, $dTimeZone);
                $sRunDateTime = $dRunDateTime->format('Y-m-d H:i:s');

                //Create battle
                echo "    Adding battle to battle table...";
                $sQuery = "INSERT INTO battle (competition_id, pool_id, round_number, datetime, official) VALUES (" . COMPETITION_ID . ", '" . $sPoolname . "', '" . $iRound . "', '" . $sRunDateTime . "', " . ($bOfficial?1:0) . ")";

                try {
                    $oResult = $oDbHelper->executeQuery($sQuery);
                    $iBattleId = $oDbHelper->getMysqli()->insert_id;
                } catch (Exception $e) {
                    $oDbHelper->printError($e->getMessage() ."\n", $sQuery);
                    die();
                }

                echo "OK!\n";
                //Read result file and add scores
                parseFile($sPoolname, $iRound, $iBattleId, $sResultsFilename);

            } else {
                throw new Exception("The filename '" . $sResultsFilename . "' cannot be found, but is mentioned in runnedbattles.json");
            }
        }
        echo "OK!\n";
    }
    echo "OK!\n";
}


/**
 * Parse result file
 */
function parseFile($sPoolId, $iRound, $iBattleId, $sFilename) {
    global $oDbHelper;

    echo "    Parsing " . $sFilename . "...\n";
    $fTemplate = fopen($sFilename, "r");
    $sContents = fread($fTemplate, filesize($sFilename));
    fclose($fTemplate);

    $aResults = str_getcsv($sContents, "\t");

    for ($aPos = 11; $aPos <= count($aResults); $aPos = $aPos + 11) {
        //Read rank and name
        $sPart1 = trim($aResults[$aPos]);

        //Read rank and make numeric
        $sRank = trim(substr($sPart1, 0, strpos($sPart1, " ") - 1));
        $sRank = preg_replace("/[^0-9,.]/", "", $sRank);

        //Read robotname
        $sTeamName = trim(substr($sPart1, strpos($sPart1, " ")));
        if ($sTeamName != "") {
            echo "      Reading robot score...";
            //Read total score
            $sTotalAll = trim($aResults[$aPos+1]);
            $iTotal = trim(substr($sTotalAll, 0, strpos($sTotalAll, "(")));
            $iTotalPerc = trim(substr($sTotalAll, strpos($sTotalAll, "(") + 1, strpos($sTotalAll, ")") - strpos($sTotalAll, "(") - 2));

            $iSurvival = trim($aResults[$aPos+2]);
            $iSurvivalBonus = trim($aResults[$aPos+3]);

            $iBulletDamage = trim($aResults[$aPos+4]);
            $iBulletBonus = trim($aResults[$aPos+5]);

            $iRamDamage = trim($aResults[$aPos+6]);
            $iRamBonus = trim($aResults[$aPos+7]);

            $iFirsts = trim($aResults[$aPos+8]);
            $iSeconds = trim($aResults[$aPos+9]);
            $iThirds = trim($aResults[$aPos+10]);

            //Find the id for the team
            $sQuery = "SELECT id FROM team WHERE fullname='" . $sTeamName . "';";
            $oResult = $oDbHelper->executeQuery($sQuery);
            $aRow = $oResult->fetch_array(MYSQLI_ASSOC);
            $sTeamId = $aRow['id'];

            //Create query
            $sQuery = "INSERT INTO battlescore (competition_id, pool_id, battle_id, team_id, rank, totalscore, totalpercentage, survivalscore, survivalbonus, bulletdamage, bulletbonus, ramdamage, rambonus, firsts, seconds, thirds) VALUES (" .
                "'" . COMPETITION_ID . "', " .
                "'" . $sPoolId . "', " .
                "'" . $iBattleId . "', " .
                "'" . $sTeamId . "', " .
                "'" . $sRank . "', " .
                "'" . $iTotal . "', " .
                "'" . $iTotalPerc . "', " .
                "'" . $iSurvival . "', " .
                "'" . $iSurvivalBonus . "', " .
                "'" . $iBulletDamage . "', " .
                "'" . $iBulletBonus . "', " .
                "'" . $iRamDamage . "', " .
                "'" . $iRamBonus . "', " .
                "'" . $iFirsts . "', " .
                "'" . $iSeconds . "', " .
                "'" . $iThirds . "'" .
                ");";

            //Run query
            $oDbHelper->executeQuery($sQuery);
            echo "OK!\n";
        }
    }
    echo "    OK!\n";
}
?>