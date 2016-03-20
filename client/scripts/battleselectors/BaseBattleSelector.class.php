<?php

class BaseBattleSelector {
    /**
     * Generate a battle file
     * sTemplate the template file
     * sFilename the output filename
     * $sTeams the teams in the battle
     */
    function generateBattleFile($sTemplate, $sFilename, $aTeams) {
        $fFile = fopen($sFilename, "w") or die("Unable to open file!");

        //Read template and write back
        $fTemplate = fopen($sTemplate, "r");
        $sContents = fread($fTemplate, filesize($sTemplate));
        fclose($fTemplate);
        fwrite($fFile, $sContents);
        //Write battles
        $sBattleString = "robocode.battle.selectedRobots=";
        foreach ($aTeams as $sTeamname) {
            $sBattleString .= $sTeamname . "*,";
        }
        fwrite($fFile, $sBattleString . "\n");
        fclose($fFile);
    }
}

?>