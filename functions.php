<?php
/**
 * Created by PhpStorm.
 * User: Chris-Campbell
 * UpDated: 11/29/2016
 * Time: 2:49 AM
 *
 */
define("GROUPFILE", "known_groups.txt");
define("GROUPINCIDENTS", "known_incidents.txt");
define("ACTIONITEMS", "Actionitems.xml");
define("AIREPORTS", "aireports.xml");


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
    $aixml = simplexml_load_file(ACTIONITEMS) or die("Error: Cannot open Action Items file");
    $timearray = []; //array to hold and sort PID by dates

    echo "List of Action Items for {$pid} in the group {$pgroup}:</br>";
    echo "-------------------";
    echo "<table width='1000'><th width='250' align='left'>Action Item</th><th align='left'>Owner</th><th align='left'>Date Created</th><th align='left'>Description</th>";

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
        echo "<tr> <td width='250'>" .$aixml->Actionitem[$key]->AIACRO .
            "&nbsp;<a href='?editAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO;
        echo "'><input type='button' value='edit' name='editAI'></a></td>";
        echo "<td width='250'>" . $aixml->Actionitem[$key]->OWNER . "</td>";
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
#   function displayFullActionItem()
#   parameters: none
#   After a user selects a an incident the full
#   incident is displayed
#   Returns: (nothing)
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
#   parameters: none
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

    echo "File Edited Successfully!<br/>";
    echo '<a href=?pid='.$pid.'&pgroup='.$pgroup.'>Back To List of Action Items for ' . $pid . 'in the group ' . $pgroup . '</a>';
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
            ID:<input type='text' name='ID'  value='$ID' disabled>
            <input type='hidden' name='ID' value='$ID'>
            <br/>
            Group:<input type='text' name='PGROUP'  value='$PGROUP' disabled>
            <input type='hidden' name='PGROUP'  value='$PGROUP'>
            <br/>
            PID:<input type='text' name='PID'  value='$pid' disabled>
            <input type='hidden' name='PID'  value='$pid'>
            <br/>
            Number:<input type='text' name='NUMBER'  value='1' disabled>
            <input type='hidden' name='NUMBER'  value='1'>
            <br/>
            AIACRO:<input type='text' name='AIACRO'  value='$newstring' disabled>
            <input type='hidden' name='AIACRO'  value='$newstring'>
            <br/>
            Owner:<input type='text' name='OWNER'>
            <br/>
            Responsible:<input type='text' name='RESPONSIBLE'>
            <br/>
            Created:<input type='text' name='CREATED' value='"; echo $objDateTime->format('d-m-Y'); echo "'>
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

        echo "Action Item added!<br/>";
        echo "<a href='add_ai.php'>Back To Main Area</a>";
    }
}
    

?>