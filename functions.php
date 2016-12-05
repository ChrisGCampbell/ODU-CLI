<?php
session_start();
/**
 * Web Portal Application
 *
 */

//Constants
define("GROUPFILE", "known_groups.txt");
define("GROUPINCIDENTS", "known_incidents.txt");
define("ACTIONITEMS", "Actionitems.xml");
define("AIREPORTS", "aireports.xml");
define("REGISTERED_USERS", "user_file.xml");


###############################################
#   function displayGroupOptions()
#   parameters: none
#   Creates the form to display group list
#
#   Returns: (nothing)
#
##############################################
function displayGroupOptions() {
    echo "<form id='groupList' method='POST' action='"; echo $_SERVER['PHP_SELF']."'>";
    echo 'Select Group:';
    getGroups();
    echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
}


###############################################
#   function getGroups()
#
#   Retrieves group list from flat file to
#   populate form in function displayGroupOptions
#
#   Returns: (nothing)
#
##############################################
function getGroups() {
    $groupNames = [];
    $fileHandler = fopen(GROUPFILE, "r");
    $groupCount = fgets($fileHandler);

    for($i=0; $i<$groupCount; $i++) {
        $groupNames[$i] =  fgets($fileHandler);
    }

    loadGroupList($groupNames);
}


###############################################
#   function loadGroupList()
#   parameters(array)
#   print group list options inside group form
#
#   Returns: (nothing)
#
##############################################
function loadGroupList($groupNames){
    for($i=0; $i<count($groupNames); $i++) {
        echo '<input type="radio" name="group" id="group" value="'.$groupNames[$i].'">'.$groupNames[$i];
    }
}


###############################################
#   function displayProjectIncidents($group)
#   parameters: takes a string representing a group
#   After a user selects a project group a list
#   of associated projects are display
#
#   Returns: (nothing)
#
##############################################
function displayProjectIncidents($selectedGroup) {
    $txtfile     = file_get_contents(GROUPINCIDENTS);
    $rows        = explode("\n", $txtfile);
    $pid = [];

    for( $i = 0; $i < count($rows); $i++ ){
        if ( strpos($rows[$i], $selectedGroup ) !== false ) {
               $projectIncidents = explode( "|", $rows[$i] );
               break;
        }
    }

    if(empty($projectIncidents)){
        echo "No current listings for <b>{$selectedGroup}</b>";
    }
    else{
        echo "List of <b>{$selectedGroup}s</b> <br/>";
        echo "----------------------";
        echo "<table width='550'>";

                //Project Incidents array beings at index 1
                for( $i = 1; $i < count($projectIncidents); $i++ ) {
                    echo "<tr> <td> <a href=?pid={$projectIncidents[$i]}&pgroup={$selectedGroup}>" . $projectIncidents[$i]."</a> </td>";
                }

        echo "</table>";
        echo "----------------------";
    }
}


###############################################
#   function displayActionItemDetails()
#   parameters: projectIncident, projectGroup
#   Display the details of the Action Item
#   Returns: (nothing)
#
##############################################
function displayActionItemDetails($pid, $pgroup) {
    $pid = trim($_GET['pid']);
    $pgroup = trim($_GET['pgroup']);
    $aixml = simplexml_load_file(ACTIONITEMS);
    $timearray = []; //array to hold and sort PID by dates

    echo "List of Action Items for {$pid} in the group {$pgroup}:</br>";
    echo "-------------------";
    echo "<table width='1000'><th width='250' align='left'>Action Item</th><th width='250' align='center'>Owner</th><th align='left'>Date Created</th><th align='left'>Description</th>";

    //Find PID dates
    for($i=0; $i<count($aixml); $i++) {
        if ($aixml->Actionitem[$i]->PGROUP == $pgroup && $aixml->Actionitem[$i]->PID == $pid) {
            $timearray[$i] = strtotime($aixml->Actionitem[$i]->CREATED);
        }
    }

    //Sort PID Date Array
    arsort($timearray);

    //output sorted PID by dates
    foreach($timearray as $key => $val) {
        echo "<tr> <td width='250'>" .$aixml->Actionitem[$key]->AIACRO . "&nbsp";
        echo "<a href='?editAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'><input type='button' value='edit' name='editAI'></a>";
        echo "<a href='?viewAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'><input type='button' value='view' name='viewAI'></a>";
        echo "<a href='?reportAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO ."'><input type='button' value='report' name='reportAI'></a>";

        echo "<td width='250' align='center'>" . $aixml->Actionitem[$key]->OWNER . "</td>";
        echo "<td width='250'>" . $aixml->Actionitem[$key]->CREATED . "</td>";
        echo "<td>" . $aixml->Actionitem[$key]->DESCRIPTION . "</td></tr><tr><td></td></tr>";
    }
    echo "</table>";

    echo "<form method='POST' action='"; echo $_SERVER['PHP_SELF']; echo "'> 
         <input type='hidden' name='pid' value='{$pid}'>
         <input type='hidden' name='pgroup' value='{$pgroup}'>
         <input type='submit' name='newActionItem' value='Create New Action Item'></form><br/><br/>";

    echo "-------------------";
}


###############################################
#   function editAI($ai)
#
#   Edit fields within a specific action item
#   parameters: 1: the AIACRO of the action item
#   Returns: (nothing)
#
##############################################
function editAI($ai) {
    $aiacronym = trim($_GET['aiacronym']);
    $aixml = simplexml_load_file(ACTIONITEMS);

    for($i=0; $i<count($aixml); $i++) {
        if($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
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

    echo "<p>Group: ". $pgroup."</p>";
    echo "<p>Incident: " . $pid . "</p>";
    echo "<p>Owner: {$owner}</p>";
    echo "<p>Date Created: {$created}</p>";
    echo "<p>Action Item: {$aiacronym}</p>";
    echo "<form method='POST' action=\""; echo $_SERVER['PHP_SELF']; echo "\">
            <p>Responsible: <input type='text' name='responsible' value='{$responsible}'></p>
            <p>Deadline: <input type='text' name='deadline' value='{$deadline}'></p>
            <p class='formfield'><label for='rationale'>Rationale:</label><textarea rows='3' cols='40' name='rationale'>{$rationale}</textarea></p>
            <p><span style='vertical-align:middle'>Description:</span><textarea rows='3' cols='40' name='description'>{$description}</textarea></p>
            <input type='hidden' name='aiacronym' value='"; echo $aiacronym; echo "'>";
    echo    '<input type="hidden" name="pid" value="'; echo $pid; echo '">';
    echo    '<input type="hidden" name="pgroup" value="'; echo $pgroup; echo '">
            <br><input type="submit" value="submit" name="submitEditAI">
          </form>';

}


###############################################
#   function viewAI($ai)
#
#   Displays the details of the Action Item as well
#   as any reports related to the action item
#   parameters: 1: AIACRO value of the action item
#   Returns: (nothing)
#
##############################################
function viewAI($ai) {

    $aiacronym = trim($_GET['aiacronym']);
    $aixml = simplexml_load_file(ACTIONITEMS);
    $aireportxml = simplexml_load_file(AIREPORTS);
    $numofreports = 0;

    for($i=0; $i<count($aixml); $i++) {
        if($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
            $id = trim($aixml->Actionitem[$i]->ID);
            $number = $aixml->Actionitem[$i]->NUMBER;
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

    echo $aiacronym . " <br/><br/>";
    echo "<b>ID:</b> " . $id . "<br/>";
    echo "<b>Project Group:</b> " . $pid . "<br/>";
    echo "<b>Number:</b> " . $number . "<br/>";
    echo "<b>Owner:</b> " . $owner . "<br/>";
    echo "<b>Responsible:</b> " . $responsible . "<br/>";
    echo "<b>Created:</b> " . $created . "<br/>";
    echo "<b>Deadline:</b> " . $deadline . "<br/>";
    echo "<b>Description:</b> " . $description . "<br/>";
    echo "<b>Rationale:</b> " . $rationale . "<br/><br/>";

    echo "Reports on this Action Item:<br/>";
    for($i=0; $i<count($aireportxml); $i++) {
        if($aireportxml->Aireport[$i]->ID == $id) {
            $numofreports = numofreports +1;
            echo "<b>ID:</b> " . $airid = $aireportxml->Aireport[$i]->ID . "<br/>";
            echo "<b>Owner:</b> " . $airowner = $aireportxml->Aireport[$i]->OWNER . "<br/>";
            echo "<b>Date:</b> " . $airdate = $aireportxml->Aireport[$i]->DATE . "<br/>";
            echo "<b>Report:</b> " . $airreport = $aireportxml->Aireport[$i]->REPORT . "<br/>";
            echo "<b>Description:</b> " . $airndescription = $aireportxml->Aireport[$i]->NDESCRIPTION . "<br/>";
            echo "<b>Deadline:</b> " . $airndeadline = $aireportxml->Aireport[$i]->NDEADLINE . "<br/>";
            echo "<b>Responsible:</b> " . $airnresponsible = $aireportxml->Aireport[$i]->NRESPONSIBLE . "<br/>";
            echo "<b>Status:</b> " . $airstatus = $aireportxml->Aireport[$i]->STATUS;
            echo "<br/><br/>";
        }
    }
    if($numofreports == 0) {
        echo "<b>Currently no reports on this item.</b><br/><br/>";
    }
    echo '<a href=?pid='.$pid.'&pgroup='.$pgroup.'>Back To List Area</a>';
}


###############################################
#   function addAiReport($ai)
#
#   Allows user to add a report
#   parameters: 1: 1: AIACRO value of the action item
#   Returns: (nothing)
#
##############################################
function addAiReportForm($ai) {
    $actionitem = trim($ai);
    $objDateTime = new DateTime('NOW');
    $ID = rand(1000,9000);
    $responsibleUsers = [];
    $actionitemxml = simplexml_load_file(ACTIONITEMS);

    for($j=0; $j<count($actionitemxml); $j++) {
        if($actionitemxml->Actionitem[$j]->AIACRO == $actionitem) {
            echo "Action item: ".$actionitemxml->Actionitem[$j]->AIACRO."<br/>";
            echo "Group: ".$actionitemxml->Actionitem[$j]->PGROUP."<br/>";
            echo "Incident: ".$actionitemxml->Actionitem[$j]->PID."<br/>";
            echo "Owner: ".$actionitemxml->Actionitem[$j]->OWNER."<br/>";
            echo "Date Created: ".$actionitemxml->Actionitem[$j]->CREATED."<br/><br/>";
        }
    }

    echo "Please input the fields to add a new report below:<br/>";
    //add new action Item coming soon.
    echo "<table width='800'>";
    echo "<form method='POST' action=\""; echo $_SERVER['PHP_SELF']; echo "\">
            
            <tr><td width='50'>ID:</td><td width='100'><input type='text' name='ID'  value='{$ID}' disabled></td></tr>
            <input type='hidden' name='ID' value='{$ID}'>
            
            <tr><td width='50'>Owner:</td><td width='100'><input type='text' name='OWNER'  value='{$_SESSION['firstname']} {$_SESSION['lastname']}'></td></tr>
            <input type='hidden' name='OWNER'  value='{$_SESSION['firstname']} {$_SESSION['lastname']}'>
            
            <tr><td width='50'>Date:</td><td width='100'><input type='text' name='DATE' value='{$objDateTime->format('d-m-Y')}'></td></tr>
            <input type='hidden' name='DATE'  value='{$objDateTime->format('d-m-Y')}'>
            
            <tr><td width='50'>Report:</td><td width='100'><input type='text' name='REPORT'  value='1' disabled></td></tr>
            <input type='hidden' name='REPORT'  value='1'>
           
            <tr><td width='50'>Deadline:</td><td width='100'><input type='text' name='NDEADLINE'></td></tr>
            
            <tr><td width='50'>Status:</td><td width='100'><input type='text' name='STATUS' value=''></td></tr>
           
            <tr><td width='50'>Deadline:</td><td width='100'><input type='text' name='DEADLINE' value=''></td></tr>
           
           <tr><td width='50'> Description:</td><td width='100'><textarea row='2' cols='40' name='NDESCRIPTION'></textarea></td></tr>
           
           <tr><td width='50'> Responsible:</td><td width='100'>";

           $responsibleUsers = getResponsibleUsers();

           echo "<select>";
           for($i=0; $i<count($responsibleUsers); $i++) {
               echo "<option name='NRESPONSIBLE' value='{$responsibleUsers[$i]}'>$responsibleUsers[$i]</option>";
           }
           echo "</td></tr>";

           echo "<tr><td width='50'></td><td width='100'><input type=\"submit\" name =\"submitNewReport\" value=\"Submit\"></td></tr>
           </form></table>";

}

###############################################
#   function getResponsibleUsers()
#   parameters: none
#   gets an array of users from
#   Returns: (array)
#
##############################################
function getResponsibleUsers() {
    $aireportsxml = simplexml_load_file(AIREPORTS);
    $responsibleUsers = [];

    for($i=0; $i<count($aireportsxml); $i++) {
        array_push($responsibleUsers, $aireportsxml->Aireport[$i]->NRESPONSIBLE);
    }

    return $responsibleUsers;
}

###############################################
#   function addNewReportToFile()
#   parameters: none
#   Add new report to report file(xml file)
#   Returns: (nothing)
#
##############################################
function addNewReportToFile() {
    //$pid = trim($_POST['PID']);
    //$pgroup = trim($_POST['PGROUP']);
    $FILENAME = AIREPORTS;

    if(file_exists($FILENAME)) {
        $airfile = fopen($FILENAME, 'r+') or die("cant open file");
        $NAI = '<Aireport>' . PHP_EOL;
        $NAI .= "<ID>" . $_POST['ID'] . "</ID>" . PHP_EOL;
        $NAI .= "<OWNER>" . $_POST['OWNER'] . "</OWNER>" . PHP_EOL;
        $NAI .= "<DATE>" . $_POST['DATE'] . "</DATE>" . PHP_EOL;
        $NAI .= "<REPORT>" . $_POST['REPORT'] . "</REPORT>" . PHP_EOL;
        $NAI .= "<NDESCRIPTION>" . $_POST['NDESCRIPTION'] . "</NDESCRIPTION>" . PHP_EOL;
        $NAI .= "<NDEADLINE>" . $_POST['NDEADLINE'] . "</NDEADLINE>" . PHP_EOL;
        $NAI .= "<NRESPONSIBLE>" . $_POST['NRESPONSIBLE'] . "</NRESPONSIBLE>" . PHP_EOL;
        $NAI .= "<STATUS>" . $_POST['STATUS'] . "</STATUS>" . PHP_EOL;
        $NAI .= "</Aireport>" . PHP_EOL;
        $NAI .= "</AIREPORTS>";

        fseek($airfile, -14, SEEK_END);
        fwrite($airfile, $NAI);
        fclose($airfile);

        echo "Report added!<br/><br/>";
        echo "<a href='add_ai.php'>Back To Project Select Area</a>";
    }
}


###############################################
#   function saveToFile()
#   parameters: description, aiacroynm, responsible,
#               rationale, deadline
#   After an action item is edited save to file
#   Returns: (nothing)
#
##############################################
function saveToFile($descr, $aia, $resp, $ration, $dead) {
    $pid = trim($_POST['pid']);
    $pgroup = trim($_POST['pgroup']);
    $description = trim($descr);
    $rationale = trim($ration);
    $responsible = trim($resp);
    $deadline = trim($dead);
    $aiacronym = trim($aia);
    $aixml = simplexml_load_file(ACTIONITEMS);

    for($i=0; $i<count($aixml); $i++) {
       if($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
           $aixml->Actionitem[$i]->DESCRIPTION = $description;
           $aixml->Actionitem[$i]->RATIONALE = $rationale;
           $aixml->Actionitem[$i]->DEADLINE = $deadline;
           $aixml->Actionitem[$i]->RESPONSIBLE = $responsible;
           $aixml->asXML(ACTIONITEMS);
       }
    }

    echo "File Edited Successfully!<br/><br/>";
    echo '<a href=?pid='.$pid.'&pgroup='.$pgroup.'>Back To List Area</a>';
}


###############################################
#   function newActionItemForm()
#   paramters: projectIncident, projectGroup
#   Display the details of the Action Item
#   Returns: (nothing)
#
##############################################
function newActionItemForm($pincident, $projectgroup) {
    $pid    = trim($pincident);
    $PGROUP = trim($projectgroup);
    $aixml = simplexml_load_file(ACTIONITEMS);
    $aiacroList = [];
    $ID = rand(1000,9999);
    $highest = [];

    for($i=0; $i<count($aixml); $i++) {
        if($aixml->Actionitem[$i]->PID == $pid) {
            array_push($aiacroList, $aixml->Actionitem[$i]->AIACRO);
        }
    }


    for($i=0; $i<count($aiacroList); $i++) {
        $position=0;
        $pilength=0;
        $positionOfIncident=0;
        $aia = $aiacroList[$i]; //example fdsfd-project1-12
        $position = stripos($aia, $pid, 0);//gets the position of word project1
        $pilength = strlen($pid); //gets the length of project1
        $positionOfIncident = $position + $pilength + 1;//finds the position of the incident # (ex. project1-)
        $substring = substr($aia, 0, $positionOfIncident); //assigns project1- to variable
        $oldvalue = intval(substr($aia, $positionOfIncident, 3));//gets the current incident #
        array_push($highest, intval($oldvalue));
    }
    $max = max($highest);//find the highest incident # last assigned
    $newvalue =  $max + 1;//returns the number after project1- + 1
    $newstring = $substring . $newvalue;
    $objDateTime = new DateTime('NOW');


    echo "<br/><br/>";

    echo "Please input the fields to add a new action item below:<br/>";
    //add new action Item coming soon.
    echo "<form method='POST' action=\""; echo $_SERVER['PHP_SELF']; echo "\">
            ID:<input type='text' name='ID'  value='{$ID}' disabled>
            <input type='hidden' name='ID' value='{$ID}'>
            <br/>
            Group:<input type='text' name='PGROUP'  value='{$PGROUP}' disabled>
            <input type='hidden' name='PGROUP'  value='{$PGROUP}'>
            <br/>
            PID:<input type='text' name='PID'  value='$pid' disabled>
            <input type='hidden' name='PID'  value='{$pid}'>
            <br/>
            Number:<input type='text' name='NUMBER'  value='1' disabled>
            <input type='hidden' name='NUMBER'  value='1'>
            <br/>
            AIACRO:<input type='text' name='AIACRO'  value='$newstring'>
            <input type='hidden' name='AIACRO'  value='{$newstring}'>
            <br/>
            Owner:<input type='text' name='OWNER' value='{$_SESSION['firstname']} {$_SESSION['lastname']}'>
            <input type='hidden' name='AIACRO'  value='{$_SESSION['firstname']} {$_SESSION['lastname']}'>
            <br/>
            Responsible:<input type='text' name='RESPONSIBLE'>
            <br/>
            Created:<input type='text' name='CREATED' value='{$objDateTime->format('d-m-Y')}'>
            <input type='hidden' name='CREATED'  value='"; echo $objDateTime->format('d-m-Y'); echo "'>
            <br/>
            Deadline:<input type='text' name='DEADLINE' value='"; echo $objDateTime->format('d-m-Y'); echo "'>
            <br/>
            Description<textarea row='2' cols='40' name='DESCRIPTION'></textarea>
            <br/>
            Rationale:<textarea row='2' cols='40' name='RATIONALE'></textarea>
            <br/>
            <input type=\"submit\" name =\"submitAddedNewActionItem\" value=\"Submit\">
            </form>";
}


###############################################
#   function addNewAIToFile()
#   parameters: none
#   After an action item is edited save to file
#   Returns: (nothing)
#
##############################################
function addNewAIToFile() {

    $pid = trim($_POST['PID']);
    $pgroup = trim($_POST['PGROUP']);
    $FILENAME = ACTIONITEMS;
    if(file_exists($FILENAME)) {
        $aifile = fopen($FILENAME, 'r+') or die("cant open file");
        $NAI = '<Actionitem>' . PHP_EOL;
        $NAI .= "<ID>" . $_POST['ID'] . "</ID>" . PHP_EOL;
        $NAI .= "<PGROUP>" . $_POST['PGROUP'] . "</PGROUP>" . PHP_EOL;
        $NAI .= "<PID>" . $_POST['PID'] . "</PID>" . PHP_EOL;
        $NAI .= "<NUMBER>" . $_POST['NUMBER'] . "</NUMBER>" . PHP_EOL;
        $NAI .= "<AIACRO>" . $_POST['AIACRO'] . "</AIACRO>" . PHP_EOL;
        $NAI .= "<OWNER>" . $_POST['OWNER'] . "</OWNER>" . PHP_EOL;
        $NAI .= "<RESPONSIBLE>" . $_POST['RESPONSIBLE'] . "</RESPONSIBLE>" . PHP_EOL;
        $NAI .= "<CREATED>" . $_POST['CREATED'] . "</CREATED>" . PHP_EOL;
        $NAI .= "<DEADLINE>" . $_POST['DEADLINE'] . "</DEADLINE>" . PHP_EOL;
        $NAI .= "<DESCRIPTION>" . $_POST['DESCRIPTION'] . "</DESCRIPTION>" . PHP_EOL;
        $NAI .= "<RATIONALE>" . $_POST['RATIONALE'] . "</RATIONALE>" . PHP_EOL;
        $NAI .= "</Actionitem>".PHP_EOL;
        $NAI .= "</ACTIONITEMS>";

        fseek($aifile, -14, SEEK_END);
        fwrite($aifile, $NAI);
        fclose($aifile);

        echo "Action Item added!<br/><br/>";
        echo '<a href=?pid='. $pid . '&pgroup=' . $pgroup . '>Back To List Area</a>';
    }
}


###############################################
#   function Verify_Session_Email_Exists()
#   parameters: none
#   Authentication of user
#   Returns: (nothing)
#
##############################################
function Verify_SignIn($search_em, $search_pwd){
    $xml = simplexml_load_file(REGISTERED_USERS);
    $search_ln=strtolower($search_em);
    $count=false;
    global $errors;

    //Search for email and password match in users file
    for($i=0; $i< count($xml); $i++){
        if (strtolower($xml->person[$i]->EML) == strtolower($search_em) && md5($search_pwd) == $xml->person[$i]->PWD){
            $count = true;
            $_SESSION['email'] = trim($xml->person[$i]->EML);//start a session store email
            $_SESSION['firstname'] = trim($xml->person[$i]->FNE);//start a session store firstname
            $_SESSION['lastname'] = trim($xml->person[$i]->LNE);//start a session store lastname
            header('Location: comments.php');
            exit();
        }//END IF

    }//end for loop

    //no match found with email and password combination
    if($count == false){
        $status_fail=array('fail');
        $errors['fail'] = "<span class=\"errors\"><b>&nbsp Email address and/or password not valid. Please try again!</span>";
        return $status_fail;
    }
}

?>