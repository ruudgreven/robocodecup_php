<?php
include_once(dirname(__FILE__) . '/../config.inc.php');

class TeamJarFile {
    private $sFilename;
    private $aClassFiles;
    private $aClasses;
    private $aPackages;

    private $sTeamFile;
    private $sTeamName;

    private $sAuthorName;
    private $sDescription;

    public function __construct($sFilename) {
        //Check if file exists
        if (!file_exists($sFilename)) {
            throw new Excpetion("The file '" . $sFilename . "' does not exists");
        }

        //Read Jar file
        $oZip = new ZipArchive;
        if ($oZip->open($sFilename, ZipArchive::CHECKCONS)) {
            $this->aClassFiles = array();
            $this->aClasses = array();
            $aTeamFiles = array();
            $this->aPackages = array();

            //Look all files and watch for team file
            for ($i = 0; $i < $oZip->numFiles; $i++) {
                $sFilename = $oZip->statIndex($i)['name'];

                // If it is a class file
                if (strpos($sFilename, ".class") !== FALSE) {
                    array_push($this->aClassFiles, $sFilename);

                    $sClass = str_replace(".class", "", $sFilename);
                    $sClass = str_replace("/", ".", $sClass);
                    $sClass = str_replace("\\", ".", $sClass);

                    array_push($this->aClasses, $sClass);
                }

                // If it is a team file
                if (strpos($sFilename, ".team") !== FALSE) {
                    array_push($aTeamFiles, $oZip->statIndex($i));
                }
            }

            //Check number of team and classfiles
            if (count($aTeamFiles) != 1) {
                throw new Exception('There should be exactly one teamfile in the jarfile, this teamfile has ' . count($aTeamFiles));
            }
            $this->sTeamFile = $aTeamFiles[0]['name'];
            $this->sTeamName = substr($this->sTeamFile, strrpos($this->sTeamFile, "/") + 1, -5);

            if (count($this->aClassFiles) == 0) {
                throw new Exception('No classfiles where found');
            }

            //Read class file packages (e.g., the locations of the classfiles)
            foreach ($this->aClassFiles as $sClassFile) {
                $sFullPackage = substr($sClassFile, 0, strrpos($sClassFile, "/"));
                $sFullPackage = str_replace("/", ".", $sFullPackage);

                //Check if this packages is already mentioned in this file
                if (!in_array($sFullPackage, $this->aPackages)) {
                    //Add to list of packages of this file
                    array_push($this->aPackages, $sFullPackage);
                }
            }

            //Read team file
            $sTeamContent = $oZip->getFromName($this->sTeamFile);
            $aLines = explode(PHP_EOL, $sTeamContent);

            //Read each line and check values
            foreach ($aLines as $sLine) {
                if (strpos($sLine, "team.members") !== FALSE) {                         //Check if team.members is a line with 4 bots comma separated;
                    $aRobots = explode(",", $sLine);

                    //Check the number of robots
                    if (count($aRobots) != TEAM_NUMBER_OF_BOTS) {
                        throw new Exception('The team description file should have exactly 4 robots, there are ' . count($aRobots));
                    }

                    //Check if all the robots described in the team file exists (e.g. There is a class with that name)
                    $aRobots[0] = str_replace("team.members=", "", $aRobots[0]);
                    foreach ($aRobots as $sRobotname) {
                        $bFound = false;

                        $sRobotname = trim($sRobotname);
                        if (substr($sRobotname, -1) == '*') { //There is a star in the robotname, remove it
                            $sRobotname = substr($sRobotname, 0, -1);
                        }

                        foreach ($this->aClasses as $sClass) {
                            //Check if the name matches
                            if ($sRobotname == $sClass) {
                                $bFound = true;
                                break;
                            }
                        }

                        if (!$bFound) {
                            throw new Exception("There is a reference to the robot " . $sRobotname . " in the teamfile, but this robot does not exists in : " . var_export($this->aClasses, true));
                        }
                    }
                } else if (strpos($sLine, "team.author.name") !== FALSE) {               //Read team.authorname and put it to the authorname field
                    $this->sAuthorName = trim(substr($sLine, strpos($sLine, "=") + 1));
                } else if (strpos($sLine, "team.description") !== FALSE) {              //Read team.authorname and put it to the authorname field
                    $this->sDescription = trim(substr($sLine, strpos($sLine, "=") + 1));
                } else if (strpos($sLine, "robocode.version") !== FALSE) {              //Read robocode version
                    $sVersion = trim(substr($sLine, strpos($sLine, "=") + 1));
                    if (ROBOCODE_VERSION != "UNDEFINED") {
                        if ($sVersion != ROBOCODE_VERSION) {
                            throw new Exception('Version mismatch, the team uses ' . $sVersion . ' but should be ' . ROBOCODE_VERSION);
                        }
                    }
                }
            }
        } else {
            throw new Exception('No valid JAR file');
        }
    }

    public function getFilename() {
        return $this->sFilename;
    }

    public function getClassFiles() {
        return $this->aClassFiles;
    }

    public function getClasses() {
        return $this->aClasses;
    }

    public function getPackages() {
        return $this->aPackages;
    }

    public function getTeamFile() {
        return $this->sTeamFile;
    }

    public function getTeamName() {
        return $this->sTeamName;
    }

    public function getAuthorName() {
        return $this->sAuthorName;
    }

    public function getDescription() {
        return $this->sDescription;
    }
}

?>