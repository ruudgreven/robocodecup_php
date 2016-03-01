<?php
include_once(dirname(__FILE__) . '/../config.inc.php');

class DBHelper {
    private $oMysqli;

    public function __construct() {
        $this->oMysqli = new mysqli(CONFIG_DB_HOSTNAME, CONFIG_DB_USERNAME, CONFIG_DB_PASSWORD, CONFIG_DB_DATABASE);
        if ($this->oMysqli->connect_errno) {
            throw new Exception("Error connection to database: " . $this->oMysqli->connect_error);
        }
    }

    /**
     * Returns a connection to mysql
     */
    function getMysqli() {
        return $this->oMysqli ;
    }

    function executeQuery($sQuery) {
        if ($oResult = $this->oMysqli->query($sQuery)) {
            return $oResult;
        } else {
            throw new Exception("Query error: " . $this->oMysqli->error);
        }
    }

    /**
     * Outputs a database result
     * @param $oResult The database result
     * @param string $sFilename The filename for the downloaded response file
     */
    function outputDbResult($oResult, $sFilename = "response") {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $sFilename . '.json');
        header("HTTP/1.1 200 OK");

        $sJson = "{\"status\": \"ok\", \"response\": [";
        $bFirst = true;
        while($aObject = $oResult->fetch_assoc()) {
            if (!$bFirst) {
                $sJson .= ",";
            } else {
                $bFirst = false;
            }
            $sJson .= json_encode($aObject);
        }
        $sJson .= "]}";

        //Print the json
        echo($sJson);
    }

    /**
     * Outputs a given array
     * @param $oResult The database result
     * @param string $sFilename The filename for the downloaded response file
     */
    function outputArray($aList, $sFilename = "response") {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $sFilename . '.json');
        header("HTTP/1.1 200 OK");

        $sJson = "{\"status\": \"ok\", \"response\": [";
        $bFirst = true;
        $sJson .= json_encode($aList);
        $sJson .= "]}";

        //Print the json
        echo($sJson);
    }

    /**
     * Outputs an error message, with statuscode 500 and json describing the error
     * @param $sMessage The error message
     */
    function outputError($sMessage, $sFilename = "error") {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $sFilename . '.json');
        header('HTTP/1.1 500 Internal Server Error');

        $sJson = "{\"status\": \"error\", \"message\": \"";
        $sJson .= json_encode($sMessage);
        $sJson .= "\"}";

        echo($sJson);
    }

    /**
     * Outputs a database result for command-line output
     * @param $oResult The database result
     */
    function printDbResult($oResult) {

        $sJson = "[\n";
        while($aObject = $oResult->fetch_assoc()) {
            $sJson .= json_encode($aObject, JSON_PRETTY_PRINT) . "\n";
        }
        $sJson .= "]\n";

        //Print the json
        echo($sJson);
    }

    /**
     * Prints an error message
     * @param $sMessage The error message
     */
    function printError($sMessage, $sQuery = "") {
        echo("\nERROR: " . $sMessage);
        if ($sQuery!="") {
            echo "\n  In query: " . $sQuery . "\n";
        }
    }
}
?>