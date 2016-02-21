<?php
include_once(dirname(__FILE__) . '/../config.inc.php');
/**
 * Reads a folder with robocode JAR files and checks the teams in it
 */

//Read input
$sFolder = $argv[1];
$sOutputfile = $argv[2];
if ($sFolder == "" || $sOutputfile == "") {
	echo("Usage:   " . $argv[0] . " <folder_with_jars> <battleconfigurationfile>\n");
	echo("Example: " . $argv[0] . " ~/Downloads ~/battleconfig.json\n");
	exit;
}

//Create output array
$aPools = array();
$aTeams = array();
$aIgnoredFiles = array();
$aAllPackages = array();

//Read all files from the directory
echo("Read files from '" . $sFolder . "'\n");
if ($hFileHandle = opendir($sFolder)) {
	while (false !== ($sFilename = readdir($hFileHandle))) {

	    //Check if the filename matches the regular expression for teamfiles
	    if (preg_match(TEAM_REGEXP, $sFilename, $aMatches)) {

            //Create a new pool when there is no pool with the given id
	        if (array_key_exists("pool_id", $aMatches)) {
                $sPoolId = $aMatches['pool_id'];

	            if (getPoolById($sPoolId) == NULL) {
                    $oPoolObject = (object) [
                        'id' => $sPoolId,
                    ];
                    array_push($aPools, $oPoolObject);
	            }
	        }

            //Create a new team
            if (array_key_exists("team_id", $aMatches)) {
                echo "  Checking team file '" . $sFilename . "'\n";
                $sTeamId = $aMatches['team_id'];

                //Read the team JAR file
                try {
                    $oTeamProperties = getTeamProperties($sFolder . "/" . $sFilename);
                    $oTeamProperties->id = $sTeamId;
                    $oTeamProperties->filename = $sFilename;
                    if ($sPoolId != null) {
                        $oTeamProperties->pool = $sPoolId;
                    }

                    array_push($aTeams, $oTeamProperties);
                } catch (Exception $e) {
                    $oIgnoredFile = (object)[
                        'filename' => $sFilename,
                        'reason' => $e->getMessage()
                    ];
                    array_push($aIgnoredFiles, $oIgnoredFile);
                }

                echo "Done!\n";
            } else {
                $oIgnoredFile = (object)[
                    'filename' => $sFilename,
                    'reason' => "The filename does not contain a valid team id"
                ];
                array_push($aIgnoredFiles, $oIgnoredFile);
            }
	    } else {
            //Add files that are ignore, but . and .. to ignored files
            if ($sFilename != "." && $sFilename != "..") {
                $oIgnoredFile = (object)[
                    'filename' => $sFilename,
                    'reason' => "The filename does not match the TEAM_REGEXP"
                ];
                array_push($aIgnoredFiles, $oIgnoredFile);
            }
        }
	}



    echo("Writing output file '" . $sOutputfile . "' ...");
    //Form output file
    $oOutput = (object) [
        'folder' => realpath($sFolder),
        'pools' => $aPools,
        'teams' => $aTeams,
        'ignoredfiles' => $aIgnoredFiles
    ];

    //Write to file
    $pFile = fopen($sOutputfile, 'w');
    fwrite($pFile, json_encode($oOutput, JSON_PRETTY_PRINT) . "\n");
    fclose($pFile);
    echo("OK!\n");

    exit();

} else {
    echo("Error: Directory '" . $sFolder . "' not found.\n");
    exit();
}

/**
 * @param $sId The id of the pool
 * @return a pool object or null when the pool does not exists
 */
function getPoolById($sId) {
    global $aPools;
    foreach ($aPools as $oPool) {
        if ($oPool->id == $sId) {
            return $oPool;
        }
    }

    return NULL;
}

function getTeamProperties($sFilename) {
    global $aAllPackages;
    echo "    Looking for team file...";

    //Check if file exists
    if (!file_exists($sFilename)) {
        throw new Exception("File not found, is the file '" . $sFilename . "' readable?");
    }

    $oZip = new ZipArchive;
    if ($oZip->open($sFilename, ZipArchive::CHECKCONS)) {
        $aClassFiles = array();
        $aTeamFiles = array();
        $aPackages = array();

        //Look all files and watch for team file
        for ($i=0; $i<$oZip->numFiles;$i++) {
            // If it is a class file
            if (strpos($oZip->statIndex($i)['name'], ".class") !== FALSE) {
                array_push($aClassFiles, $oZip->statIndex($i));
            }

            // If it is a team file
            if (strpos($oZip->statIndex($i)['name'], ".team") !== FALSE) {
                array_push($aTeamFiles, $oZip->statIndex($i));
            }
        }

        //Check number of team and classfiles
        if (count($aTeamFiles) != 1) {
            throw new Exception('There should be exactly one teamfile in the jarfile, this teamfile has ' . count($aTeamFiles));
        }
        if (count($aClassFiles) == 0) {
            throw new Exception('No classfiles where found');
        }

        //Read class file packages (e.g., the locations of the classfiles)
        foreach ($aClassFiles as $aClassFile) {
            $sClassFile = $aClassFile['name'];
            $sFullPackage = substr($sClassFile, 0, strrpos($sClassFile, "/"));
            $sFullPackage = str_replace("/", ".", $sFullPackage);

            //Check if this packages is already mentioned in this file
            if (!in_array($sFullPackage, $aPackages)) {
                //Add to list of packages of this file
                array_push($aPackages, $sFullPackage);
            }
        }


        //Check packages uniqueness
        foreach ($aPackages as $sPackage) {
            if (in_array($sPackage, $aAllPackages)) {
                throw new Exception("The package name '" . $sPackage . "' is not unique. It is used in another file.");
            } else {
                //Add to global list of packages
                array_push($aAllPackages, $sPackage);
            }
        }

        //Read team file
        $sTeamContent = $oZip->getFromName($aTeamFiles[0]['name']);
        $aLines = explode(PHP_EOL, $sTeamContent);

        //Create variables for team properties;
        $sAuthorName = "";
        $sDescription = "";

        //Read each line and check values
        foreach ($aLines as $sLine) {
            if (strpos($sLine, "team.members") !== FALSE) {                         //Check if team.members is a line with 4 bots comma separated;
                $aRobots = explode(",", $sLine);
                if (count($aRobots) != TEAM_NUMBER_OF_BOTS) {
                    throw new Exception('The team description file should have exactly 4 robots, there are ' . count($aRobots));
                }
            } else if (strpos($sLine, "team.author.name") !== FALSE) {               //Read team.authorname and put it to the authorname field
                $sAuthorName = trim(substr($sLine, strpos($sLine, "=") + 1));
            } else if (strpos($sLine, "team.description") !== FALSE) {              //Read team.authorname and put it to the authorname field
                $sDescription = trim(substr($sLine, strpos($sLine, "=") + 1));
            } else if (strpos($sLine, "robocode.version") !== FALSE) {              //Read robocode version
                $sVersion = trim(substr($sLine, strpos($sLine, "=") + 1));
                if (ROBOCODE_VERSION != "UNDEFINED") {
                    if ($sVersion != ROBOCODE_VERSION) {
                        throw new Exception('Version mismatch, the team uses ' . $sVersion . ' but should be ' . ROBOCODE_VERSION);
                    }
                }
            }
        }

        //Create return object
        $oOutput = (object) [
            'authorname' => $sAuthorName,
            'description' => $sDescription,
            'packages' => $aPackages
        ];
        return $oOutput;
    } else {
        throw new Exception('No valid JAR file');
    }
}
?>