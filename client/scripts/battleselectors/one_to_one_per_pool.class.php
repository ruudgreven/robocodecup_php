<?php

class BattleSelector extends BaseBattleSelector {
    private $sOutputFolder;
    private $aPoolBattles;

    public function __construct($sOutputFolder) {
        $this->sOutputFolder = $sOutputFolder;
    }

    function generateBattles($aPools) {
        //Generate battles for every pool
        $this->aPoolBattles = array();
        foreach ($aPools as $sPoolname => $aPool) {
            $this->aPoolBattles[$sPoolname] = array();

            foreach ($aPool as $oTeam) {
                //Read the teamfile and create the correct format (with dots and without .team)
                $sTeamfile = str_replace("/", ".", $oTeam->teamfile);
                $sTeamfile = substr($sTeamfile, 0, -5);

                $oTeamArr1 = (object) [
                    'id' => $oTeam->id,
                    'teamfile' => $sTeamfile
                ];

                //Add a match to all other teams in this pool
                foreach ($aPool as $oTeam2) {
                    if ($oTeam->id != $oTeam2->id) {
                        //Read the teamfile of the other team and create the correct format (with dots and without .team)
                        $sTeamfile2 = str_replace("/", ".", $oTeam2->teamfile);
                        $sTeamfile2 = substr($sTeamfile2, 0, -5);
                        $sFilenameStart = $sPoolname . "_" . $oTeam->id . "-" . $oTeam2->id;

                        $oTeamArr2 = (object) [
                            'id' => $oTeam2->id,
                            'teamfile' => $sTeamfile2
                        ];

                        //Create a battle
                        $oBattle = (object) [
                            'teams' => [$oTeamArr1, $oTeamArr2],
                            'filename_battle_singleround' => "battles/" . $sFilenameStart . "_singleround.battle",
                            'filename_battle_tenrounds' => "battles/" . $sFilenameStart . "_tenrounds.battle",
                            'filename_results_tenrounds' => "output/" . $sFilenameStart . "_tenrounds.results",
                            'filename_replay_tenrounds' => "output/" . $sFilenameStart . "_tenrounds.br",
                        ];

                        //Add the battle
                        array_push($this->aPoolBattles[$sPoolname], $oBattle);
                    }
                }
            }
        }
    }

    function showBattles() {
        echo "\nThe following battles will be played:\n";
        foreach ($this->aPoolBattles as $sPoolname => $aPoolBattle) {
            echo("        In pool '" . $sPoolname . "':\n");
            foreach ($aPoolBattle as $oBattle) {
                echo("          " . $oBattle->teams[0]->id . " - " . $oBattle->teams[1]->id . "\n");
            }
        }
    }

    function writeRunnedBattles() {
        echo("Writing json file 'runnedbattles.json' ...");

        //Write to file
        $pFile = fopen($this->sOutputFolder . "/runnedbattles.json", 'w');
        fwrite($pFile, json_encode($this->aPoolBattles, JSON_PRETTY_PRINT) . "\n");
        fclose($pFile);
        echo("OK!\n");
    }

    function writeBattleFiles() {
        foreach ($this->aPoolBattles as $sPoolname => $aPoolBattle) {
            foreach ($aPoolBattle as $oBattle) {
                echo "  Generating " . $sPoolname . ": " . $oBattle->teams[0]->id . " - " . $oBattle->teams[1]->id . "\n";

                //Configure filenames
                $sTemplateFilenameStart = "../" . TEMPLATE_FOLDER . "/";
                $aTeams = [$oBattle->teams[0]->teamfile, $oBattle->teams[1]->teamfile];

                //Build battle files based upon the templates
                $this->generateBattleFile($sTemplateFilenameStart . "singleround.battle", $this->sOutputFolder . "/" . $oBattle->filename_battle_singleround, $aTeams);
                $this->generateBattleFile($sTemplateFilenameStart . "tenrounds.battle", $this->sOutputFolder . "/" . $oBattle->filename_battle_tenrounds, $aTeams);
            }
        }
    }
}
