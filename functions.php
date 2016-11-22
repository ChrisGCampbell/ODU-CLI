<?php
/**
 * Created by PhpStorm.
 * User: Chris-Campbell
 * Date: 11/1/2016
 * Time: 7:19 PM
 */
define("GROUPFILE", "known_groups.txt");
define("GROUPINCIDENTS", "known_incidents.txt");
define("ACTIONITEMS", "Actionitems.xml");
define("AIREPORTS", "aireports.xml");


###############################################
#   function displayGroupOptions()
#
#   Creates the form to display group list
#
#
#   Returns: null
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
#   Returns: null
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
#
#   print group list options inside group form
#
#
#   Returns: null
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
#   Returns: null
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
#
#   Display the details of the Action Item
#   Returns: null
#
##############################################
function displayActionItemDetails($pid, $pgroup) {
    $pid = trim($_GET['pid']);
    $pgroup = trim($_GET['pgroup']);
    echo "Lis of Action Items for {$pid} in the group ($pgroup}:</br>";
    echo "-------------------";
    echo "<table width='500'><th align='left'>Action Item</th><th align='left'>Owner</th><th align='left'>Description</th>";

    $aixml = simplexml_load_file(ACTIONITEMS) or die("Error: Cannot open Action Items file");
    for($i=0; $i<count($aixml); $i++) {
        if ($aixml->Actionitem[$i]->PGROUP == $pgroup && $aixml->Actionitem[$i]->PID == $pid) {
            echo "<tr> <td>" .$aixml->Actionitem[$i]->AIACRO;
            echo $aixml->Actionitem[$i]->OWNER;
            echo $aixml->Actionitem[$i]->DESCRIPTION . "</td></tr><tr><td></td></tr>";
        }
    }
    echo "</table>";
    echo "-------------------";
}



###############################################
#   function displayFullActionItem()
#
#   After a user selects a an incident the full
#   incident is displayed
#   Returns: null
#
##############################################
function displayFullActionItem( $group, $acronym, $incident ) {
    $groupName = trim($_GET['group']);
    $aiacronym = trim($_GET['aiacronym']);
    $incidentNumber = trim($_GET['incident']);

    echo "<p>Action item for {$groupName} {$aiacronym}</p>";
    echo '<p><a href=?newActionItem=true>Add New Action Item</a> | ';
    echo "<a href=?editAI=true&aiacronym=$aiacronym>Edit</a> | ";
    echo "<a href='#'>Report</a></p>";

    $aixml = simplexml_load_file(ACTIONITEMS) or die("Error: Cannot open Action Items file");
    for($i=0; $i<count($aixml); $i++) {
        if ($aixml->Actionitem[$i]->GROUP == $groupName && $aixml->Actionitem[$i]->AIACRO == $aiacronym) {
            echo "AIACRO: " .$aixml->Actionitem[$i]->AIACRO . "<br/>";
            echo "OWNER: " .$aixml->Actionitem[$i]->OWNER . "<br/>";
            $aireportxml = simplexml_load_file(AIREPORTS) or die("Error: Cannot open AIReports file");
            for($j=0; $j<count($aireportxml); $j++) {
                if ($aireportxml->Aireport[$j]->AIID == $incidentNumber) {
                    echo "DESCRIPTION: " . $aireportxml->Aireport[$j]->NDESCRIPTION . "<br/>";
                    echo "STATUS: " . $aireportxml->Aireport[$j]->STATUS . "<br/>";
                }
            }
            echo "DEADLINE: " .$aixml->Actionitem[$i]->DEADLINE . "<br/>";
        }
    }
}



###############################################
#   function editAI()
#
#   Edit fields within a specific action item
#   Returns: null
#
##############################################
function editAI($ai) {
    $aiacronym = trim($_GET['aiacronym']);
    $aixml = simplexml_load_file(ACTIONITEMS);

    for($i=0; $i<count($aixml); $i++) {
        if($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
            $description = $aixml->Actionitem[$i]->DESCRIPTION;
            $group = $aixml->Actionitem[$i]->GROUP;
            $owner = $aixml->Actionitem[$i]->OWNER;
            $created = $aixml->Actionitem[$i]->CREATED;
            $deadline = $aixml->Actionitem[$i]->DEADLINE;
            break;
        }
    }

    echo "Edit description details for ".$group." ".$aiacronym;
    echo "<p>Owner: {$owner}</p>";
    echo "<p>Date Created: {$created}</p>";
    echo "<p>Deadline: {$deadline}</p>";
    echo "<form method='POST' action=\""; echo $_SERVER['PHP_SELF']; echo "\">
            <textarea rows='4' cols='50' name='description'>{$description}</textarea>
            <input type=\"hidden\" name=\"aiacronym\" value=\""; echo $aiacronym; echo "\">
            <br><input type=\"submit\" value=\"submit\" name=\"submitEditAI\">
          </form>";

}



###############################################
#   function saveToFile()
#
#   After an action item is edited save to file
#   Returns: null
#
##############################################
function saveToFile($descr, $aia) {
    $description = trim($descr);
    $aiacronym = trim($aia);
    $aixml = simplexml_load_file(ACTIONITEMS);

    for($i=0; $i<count($aixml); $i++) {
       if($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
           $aixml->Actionitem[$i]->DESCRIPTION = $description;
           $aixml->asXML(ACTIONITEMS);
       }
    }

    echo "File Edited Successfully!";
}


function newActionItemForm($aiac) {
    $aiacro = trim($aiac);
    echo "Please input the fields to add a new action item below:<br/>";
    //add new action Item coming soon.
    echo "<form method='POST' action=\""; echo $_SERVER['PHP_SELF']; echo "\">
            ID:<input type='text' name='ID'>
            <br/>
            Group:<input type='text' name='GROUP'>
            <br/>
            PID:<input type='text' name='PID'>
            <br/>
            AIACRO:<input type='text' name='AIACRO'>
            <br/>
            Owner:<input type='text' name='OWNER'>
            <br/>
            Responsible:<input type='text' name='RESPONSIBLE'>
            <br/>
            Created:<input type='text' name='CREATED'>
            <br/>
            Deadlline:<input type='text' name='DEADLINE'>
            <br/>
            Description<input type='text' name='DESCRIPTION'>
            <br/>
            Rationale:<input type='text' name='RATIONALE'>
            <br/>
            <input type=\"submit\" name =\"submitAddedNewActionItem\" value=\"Submit\">
            </form>";
}


function addNewAIToFile() {
    $FILENAME = ACTIONITEMS;
    if(file_exists($FILENAME)) {
        $aifile = fopen($FILENAME, 'r+') or die("cant open file");
        $NAI = '<Actionitem>' . PHP_EOL;
        $NAI .= "<ID>" . $_POST['ID'] . "</ID>" . PHP_EOL;
        $NAI .= "<PID>" . $_POST['PID'] . "</PID>" . PHP_EOL;
        $NAI .= "<AIACRO>" . $_POST['AIACRO'] . "</AIACRO>" . PHP_EOL;
        $NAI .= "<OWNER>" . $_POST['OWNER'] . "</OWNER>" . PHP_EOL;
        $NAI .= "<RESPONSIBLE>" . $_POST['RESPONSIBLE'] . "</RESPONSIBLE>" . PHP_EOL;
        $NAI .= "<CREATED>" . $_POST['CREATED'] . "</CREATED>" . PHP_EOL;
        $NAI .= "<DEADLINE>" . $_POST['DEADLINE'] . "</DEADLINE>" . PHP_EOL;
        $NAI .= "<DESCRIPTION>" . $_POST['DESCRIPTION'] . "</DESCRIPTION>" . PHP_EOL;
        $NAI .= "<RATIONALE>" . $_POST['RATIONALE'] . "</RATIONALE>" . PHP_EOL;
        $NAI .= "<Actionitem>" . PHP_EOL . "</ACTIONITEMS>";

        fseek($aifile, 0, SEEK_END);
        fwrite($aifile, $NAI);
        fclose($aifile);

        echo "Action Item added!";
    }
}
    

?>