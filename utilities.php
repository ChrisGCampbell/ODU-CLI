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
    static $pid;
    static $pgroup;

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

    public function setPGROUP($pgroup) {
        $this->pgroup = $pgroup;
    }

    public function setPID($pid) {
        $this->pid = $pid;
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

    public function displayProjectIncidentList()
    {
        if( !isset($_POST['group']) ) {
            echo "You did not select a group.<br/><a href='actionitems.php'>Back to Group Area</a>";
            exit();
        }
        else {
            $this->setPGROUP(trim($_POST['group']));
            $txtfile = file_get_contents(GROUPINCIDENTS);

                try {
                    if ($txtfile == false) {
                        throw new customException("Failed to open Group Incidents on ");
                    }
                }
                catch(customException $e) {
                    echo $e->errorMessage();
                }

            $rows = explode("\n", $txtfile);
            $pid = [];

            for ($i = 0; $i < count($rows); $i++) {
                if ( strpos( $rows[$i], $this->pgroup ) !== false ) {
                    $projectIncidents = explode( "|", $rows[$i] );
                    break;
                }
            }

            if ( empty($projectIncidents) ) {
                echo "No current listings for " . $this->pgroup;
            }
            else {
                echo "List of <b>".$this->pgroup."s</b> <br/>";
                echo "----------------------";
                echo "<table width='550'>";
                //Project Incidents array beings at index 1
                for ($i = 1; $i < count($projectIncidents); $i++) {
                    echo '<tr> <td> <a href="?pid='.$projectIncidents[$i].'&amp;pgroup='.$this->pgroup.'">'.$projectIncidents[$i].'</a> </td></tr>';
                }
                echo "</table>";
                echo "----------------------";
                echo "<br/><br/><a href='actionitems.php'>Back to Group Selection Area.</a>";
            }
        }
    }

    public function displayActionItemDetails()
    {
        $aixml = simplexml_load_file(ACTIONITEMS);
        $timearray = []; //array to hold and sort PID by dates

        echo "List of Action Items for {$this->pid} in the group {$this->pgroup}:</br>";
        echo "-------------------";
        echo "<table width='1000'><th width='300' align='left'>Action Item</th><th width='250' align='center'>Owner</th><th align='left'>Date Created</th><th align='left'>Description</th>";

        //Find PID dates
        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->PGROUP == $this->pgroup && $aixml->Actionitem[$i]->PID == $this->pid) {
                $timearray[$i] = strtotime($aixml->Actionitem[$i]->CREATED);
            }
        }

        //Sort PID Date Array
        arsort($timearray);

        //output sorted PID by dates
        foreach ($timearray as $key => $val) {
            echo "<tr> <td width='250'>" . $aixml->Actionitem[$key]->AIACRO . "&nbsp";
            echo "<a href='?editAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'><input type='button' value='edit' name='editAI'></a>";
            echo "<a href='?viewAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'><input type='button' value='view' name='viewAI'></a>";
            echo "<a href='?reportAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'><input type='button' value='report' name='reportAI'></a>";

            echo "<td width='250' align='center'>" . $aixml->Actionitem[$key]->OWNER . "</td>";
            echo "<td width='250'>" . $aixml->Actionitem[$key]->CREATED . "</td>";
            echo "<td>" . $aixml->Actionitem[$key]->DESCRIPTION . "</td></tr><tr><td></td></tr>";
        }
        echo "</table>";

        echo "<form method='POST' action='";
        echo $_SERVER['PHP_SELF'];
        echo "'> 
         <input type='hidden' name='pid' value='{$this->pid}'>
         <input type='hidden' name='pgroup' value='{$this->pgroup}'>
         <input type='submit' name='newActionItem' value='Create New Action Item'></form><br/><br/>";

        echo "-------------------";
    }

    public function editAI()
    {
        $aiacronym = trim($_GET['aiacronym']);
        $aixml = simplexml_load_file(ACTIONITEMS);

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
                $description = $aixml->Actionitem[$i]->DESCRIPTION;
                $pgroup = $aixml->Actionitem[$i]->PGROUP;
                $pid = $aixml->Actionitem[$i]->PID;
                $owner = $aixml->Actionitem[$i]->OWNER;
                $created = $aixml->Actionitem[$i]->CREATED;
                $deadline = $aixml->Actionitem[$i]->DEADLINE;
                $responsible = $aixml->Actionitem[$i]->RESPONSIBLE;
                $rationale = $aixml->Actionitem[$i]->RATIONALE;
                break;
            }
        }

        echo "<p>Group: " . $pgroup . "</p>";
        echo "<p>Incident: " . $pid . "</p>";
        echo "<p>Owner: {$owner}</p>";
        echo "<p>Date Created: {$created}</p>";
        echo "<p>Action Item: {$aiacronym}</p>";
        echo "<table width='800'>";
        echo "<form method='POST' action=\"";
        echo $_SERVER['PHP_SELF'];
        echo "\">
            <tr><td width='50'>Responsible:</td><td width='100'> <input type='text' name='responsible' value='{$responsible}'></td></tr>
            <tr><td width='50'>Deadline:</td><td width='100'> <input type='text' name='deadline' value='{$deadline}'></td></tr>
            <tr><td width='50'>Rationale:</td><td width='100'> <textarea rows='3' cols='40' name='rationale'>{$rationale}</textarea></td></tr>
            <tr><td width='50'>Description:</td><td width='100'><textarea rows='3' cols='40' name='description'>{$description}</textarea></td></tr>
            <input type='hidden' name='aiacronym' value='";
        echo $aiacronym;
        echo "'>";
        echo '<input type="hidden" name="pid" value="';
        echo $pid;
        echo '">';
        echo '<input type="hidden" name="pgroup" value="';
        echo $pgroup;
        echo '">
            <tr><td width=\'50\'></td><td><input type="submit" value="submit" name="submitEditAI"></td></tr>
          </form></table>';

    }

    public function saveToFile($descr, $aia, $resp, $ration, $dead)
    {

        $pid = trim($_POST['pid']);
        $pgroup = trim($_POST['pgroup']);
        $description = trim($descr);
        $rationale = trim($ration);
        $responsible = trim($resp);
        $deadline = trim($dead);
        $aiacronym = trim($aia);
        $aixml = simplexml_load_file(ACTIONITEMS);

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
                $aixml->Actionitem[$i]->DESCRIPTION = $description;
                $aixml->Actionitem[$i]->RATIONALE = $rationale;
                $aixml->Actionitem[$i]->DEADLINE = $deadline;
                $aixml->Actionitem[$i]->RESPONSIBLE = $responsible;
                $aixml->asXML(ACTIONITEMS);
            }
        }

        echo "File Edited Successfully!<br/><br/>";
        echo '<a href=?pid=' . $pid . '&pgroup=' . $pgroup . '>Back To List Area</a>';
    }
}
final class customException extends Exception {
    public function errorMessage() {
        $currentTime = date('H:i, jS F Y');
        $errorfile = fopen("DataFiles/error-file.txt", "a");
        $errormessage = $this->getMessage() . " " . $currentTime;
        fwrite($errorfile,$errormessage);
        fclose($errorfile);
        echo "An error has occurred. The error has been recorded and sent to the system administror.";
        exit();
    }
}