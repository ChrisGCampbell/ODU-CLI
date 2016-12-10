<?php
#######################################
# Class file for ActionReports Software
# Updated by Christopher Campbell
# 12/10/2016
#######################################
define("GROUPFILE", "DataFiles/known_groups.txt");
define("GROUPINCIDENTS", "DataFiles/known_incidents.txt");
define("ACTIONITEMS", "DataFiles/Actionitems.xml");
define("AIREPORTS", "DataFiles/aireports.xml");
define("REGISTERED_USERS", "DataFiles/user_file.xml");
error_reporting(0);

final class ActionReports {
    private $_firstname;
    private $_lastname;
    private $_emailAddress;

    // constructor
    public function __construct() {

        $this->_firstname = $_SESSION['firstname'];
        $this->_lastname = $_SESSION['lastname'];
        $this->_emailAddress = $_SESSION['email'];
    }

    public function getFullName() {
        return $this->_firstname . " " . $this->_lastname;
    }

    public function getUserEmailAddress() {
        return $this->_emailAddress;
    }


    public function displayGroupSelectionList() {
        $groupNames = [];
        $fileHandler = fopen(GROUPFILE, "r");
        $groupCount = fgets($fileHandler);

        try {
            if (!$fileHandler) {
                throw new customException("Failed to open groupfile on ");
            }
        }
        catch(customException $e) {
            echo $e->errorMessage();
        }

        echo "<form id='groupList' method='POST' action='";echo $_SERVER['PHP_SELF'] . "'>";

        echo 'Select Group:';
        for ($i = 0; $i < $groupCount; $i++) {
            echo '<input type="radio" name="group" id="group" value="'; echo  $groupNames[$i] = fgets($fileHandler); echo  '">' . $groupNames[$i];
        }

        echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
    }

    function displayProjectIncidentList()
    {
        define(GROUP, $_POST['group']);
        $txtfile = file_get_contents(GROUPINCIDENTS);
        $rows = explode("\n", $txtfile);
        $pid = [];

        for ($i = 0; $i < count($rows); $i++) {
            if (strpos($rows[$i], GROUP) !== false) {
                $projectIncidents = explode("|", $rows[$i]);
                break;
            }
        }

        if (empty($projectIncidents)) {
           echo "No current listings for <b>".GROUP."</b>";
        } else {
            echo "List of <b>".GROUP."s</b> <br/>";
            echo "----------------------";
            echo "<table width='550'>";

            //Project Incidents array beings at index 1
            for ($i = 1; $i < count($projectIncidents); $i++) {
                echo "<tr> <td> <a href=?pid={$projectIncidents[$i]}&pgroup={GROUP}>" . $projectIncidents[$i] . "</a> </td>";
            }

            echo "</table>";
            echo "----------------------";
        }
    }

}


final class customException extends Exception {
    public function errorMessage() {
        $currentTime = date('H:i, jS F Y');
        $errorfile = fopen("DataFiles/error-file.txt", "a");
        $errormessage = $this->getMessage() . " " . $currentTime;
        fwrite($errorfile,$errormessage);
        fclose($errorfile);
        echo "An error has occurred. Please contact the system administror.";
        exit();
    }
}