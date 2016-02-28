<?php
require_once(dirname(__FILE__) . '/../../common/config.inc.php');
require_once(dirname(__FILE__) . '/../../common/classes/TeamJarFile.class.php');

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
                    $oTeamJarFile = new TeamJarFile($sFolder . "/" . $sFilename);

                    //Check packages uniqueness
                    foreach ($oTeamJarFile->getPackages() as $sPackage) {
                        if (in_array($sPackage, $aAllPackages)) {
                            throw new Exception("The package name '" . $sPackage . "' is not unique. It is used in another file.");
                        } else {
                            //Add to global list of packages
                            array_push($aAllPackages, $sPackage);
                        }
                    }

                    //Create team object
                    $oTeam = (object)[
                        'id' => $sTeamId,
                    ];
                    $oTeam->filename = $sFilename;
                    if ($sPoolId != null) {
                        $oTeam->pool = $sPoolId;
                    }
                    $oTeam->teamfile = $oTeamJarFile->getTeamFile();
                    $oTeam->teamname = $oTeamJarFile->getTeamName();
                    $oTeam->authorname = $oTeamJarFile->getAuthorName();
                    $oTeam->description = $oTeamJarFile->getDescription();
                    $oTeam->packages = $oTeamJarFile->getPackages();
                    $oTeam->classes = $oTeamJarFile->getClasses();

                    //Add team object to array of teams
                    array_push($aTeams, $oTeam);
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
?>