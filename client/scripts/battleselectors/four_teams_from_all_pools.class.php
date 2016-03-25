<?php
class Count {
    public $oTeam;
    public $iCount;

    public function __construct($oTeam) {
        $this->oTeam = $oTeam;
        $this->iCount = 0;
    }

    static function compare($oCountA, $oCountB) {
        return $oCountA->iCount - $oCountB->iCount;
    }
}

class BattleSelector extends BaseBattleSelector {
    private $sOutputFolder;
    private $aBattlesToPlay;
    private $iNumberOfBattlesPerTeam;


    public function __construct($sOutputFolder, $aSelectorOptions) {
        $this->sOutputFolder = $sOutputFolder;
        $this->aBattlesToPlay = array();

        if (sizeof($aSelectorOptions) == 0) {
            die("Battleselector expects 1 argument: minbattlenum=<num>\n");
        }
        if (strpos($aSelectorOptions[0], "minbattlenum=") === FALSE) {
            die("Battleselector expects 1 argument: minbattlenum=<num>\n");
        }
        $this->iNumberOfBattlesPerTeam = substr($aSelectorOptions[0], 13);

        if (!is_numeric($this->iNumberOfBattlesPerTeam)) {
            die("Argument for battleselector not ok, should be minbattlenum=<num>\n");
        }
    }

    function generateBattles($aPools) {
        //Generate battles for every pool
        $aTeamCounts = array();

        //Add all teams to a flat array
        foreach ($aPools as $sPoolname => $aPool) {
            foreach ($aPool as $oTeam) {
                array_push($aTeamCounts,new Count($oTeam));
            }
        }

        //Do while every team has played more than the mininum number of battles

        do {
            //Shuffle teams
            shuffle($aTeamCounts);

            //Walk through team
            do {
                $aToPlay = array();
                $aCounts = array();
                //Pick the first four from the list and play a battle
                for($i = 0; $i < 4; $i++ ) {
                    $aTeamCounts[$i]->iCount++;
                    array_push($aToPlay, $aTeamCounts[$i]);
                }

                $aTeams = array();
                foreach($aToPlay as $oCount) {
                    $sTeamfile = str_replace("/", ".", $oCount->oTeam->teamfile);
                    $sTeamfile = substr($sTeamfile, 0, -5);

                    $oTeam = (object) [
                        'id' => $oCount->oTeam->id,
                        'teamfile' => $sTeamfile
                    ];

                    array_push($aTeams, $oTeam);
                }
                $sFilenameStart = $sPoolname . "_" . $aTeams[0]->id . "-" . $aTeams[1]->id . "-" . $aTeams[2]->id . "-" . $aTeams[3]->id;

                //Create battle
                $oBattle = (object) [
                    'teams' => $aTeams,
                    'filename_battle_singleround' => "battles/" . $sFilenameStart . "_singleround.battle",
                    'filename_battle_tenrounds' => "battles/" . $sFilenameStart . "_tenrounds.battle",
                    'filename_results_tenrounds' => "output/" . $sFilenameStart . "_tenrounds.results",
                    'filename_replay_tenrounds' => "output/" . $sFilenameStart . "_tenrounds.br",
                ];
                array_push($this->aBattlesToPlay, $oBattle);

                //Sort the collections, less played battles first in the array
                usort($aTeamCounts, array("Count", "compare"));
            } while (!$this->equalCounts($aTeamCounts));
        } while ($aTeamCounts[0]->iCount < $this->iNumberOfBattlesPerTeam);

        //Set the number of battles played per team
        $this->iNumberOfBattlesPerTeam = $aTeamCounts[0]->iCount;

    }

    function equalCounts($aTeamCounts) {
        $iFirst = $aTeamCounts[0]->iCount;
        foreach ($aTeamCounts as $oCount) {
            if ($oCount->iCount != $iFirst) {
                return false;
            }
        }
        return true;
    }


    function showBattles() {
        echo "\nThe following battles will be played:\n";
        foreach ($this->aBattlesToPlay as $oBattle) {
            echo("     " . $oBattle->teams[0]->id . " - " . $oBattle->teams[1]->id . " - " . $oBattle->teams[2]->id . " - " . $oBattle->teams[3]->id . "\n");
        }
        echo "\nThere will be " . $this->iNumberOfBattlesPerTeam . " battles per team, which makes a total of " . sizeof($this->aBattlesToPlay) . "\n";
    }

    function writeRunnedBattles() {
        echo("Writing json file 'runnedbattles.json' ...");

        $aPools = array();
        $aPools['ALL'] = $this->aBattlesToPlay;
        //Write to file
        $pFile = fopen($this->sOutputFolder . "/runnedbattles.json", 'w');
        fwrite($pFile, json_encode($aPools, JSON_PRETTY_PRINT) . "\n");
        fclose($pFile);
        echo("OK!\n");
    }

    function writeBattleFiles() {
        foreach ($this->aBattlesToPlay as $oBattle) {
            echo "  Generating " . $oBattle->teams[0]->id . " - " . $oBattle->teams[1]->id . " - " . $oBattle->teams[2]->id . " - " . $oBattle->teams[3]->id . "\n";

            //Configure filenames
            $sTemplateFilenameStart = "../" . TEMPLATE_FOLDER . "/";
            $aTeams = [$oBattle->teams[0]->teamfile, $oBattle->teams[1]->teamfile, $oBattle->teams[2]->teamfile, $oBattle->teams[3]->teamfile];

            //Build battle files based upon the templates
            $this->generateBattleFile($sTemplateFilenameStart . "singleround_fourteams.battle", $this->sOutputFolder . "/" . $oBattle->filename_battle_singleround, $aTeams);
            $this->generateBattleFile($sTemplateFilenameStart . "tenrounds_fourteams.battle", $this->sOutputFolder . "/" . $oBattle->filename_battle_tenrounds, $aTeams);
        }
    }
}
