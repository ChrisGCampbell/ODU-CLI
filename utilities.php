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

class ActionReports {
    protected $_firstname;
    protected $_lastname;

    // constructor
    public function __construct() {

        $this->_firstname = $_SESSION['firstname'];
        $this->_lastname = $_SESSION['lastname'];
    }

    protected function getFullName() {
        return $this->_firstname . " " . $this->_lastname;
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


        for ($i = 0; $i < $groupCount; $i++) {
            echo '<input type="radio" name="group" id="group" value="'; echo  $groupNames[$i] = fgets($fileHandler); echo  '">' . $groupNames[$i];
        }

        echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
    }

}


class customException extends Exception {
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