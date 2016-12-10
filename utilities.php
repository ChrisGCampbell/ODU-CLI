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


    protected function displayGroupSelectionList() {
        $groupNames = [];
        $fileHandler = fopen(GROUPFILE, "r");
        $groupCount = fgets($fileHandler);

        for ($i = 0; $i < $groupCount; $i++) {
            $groupNames[$i] = fgets($fileHandler);
        }

        for ($i = 0; $i < count($groupNames); $i++) {
            echo '<input type="radio" name="group" id="group" value="' . $groupNames[$i] . '">' . $groupNames[$i];
        }
    }
}


