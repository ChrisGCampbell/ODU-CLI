<?php
/**
 * Created by PhpStorm.
 * User: Chris-Campbell
 * Date: 11/1/2016
 * Time: 7:19 PM
 */
define("GROUPFILE", "known_groups.txt");
define("GROUPINCIDENTS", "known_incidents.txt");
define("ACTIONITEMS", "ais.xml");
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
#   Retrieves group list from flat file
#
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
#   function displayListOfAIS()
#
#   After a user selects a project group a list
#   of associated actions items are display
#   specific to that group
#   Returns: null
#
##############################################
function displayListOfAIS($selectedGroup) {
    $txtfile     = file_get_contents(GROUPINCIDENTS);
    $rows        = explode("\n", $txtfile);
    $aixml       = simplexml_load_file(ACTIONITEMS);
    $aireportxml = simplexml_load_file(AIREPORTS);


    for($i=0; $i<count($rows); $i++){
        if (strpos($rows[$i], $selectedGroup) !== false) {
               $aiItems = explode("|",$rows[$i]);
               break;
        }
    }

    //$aiItems AI-Acronym's being at index 1
    echo "<table width='550'><col width='200'><col width='100'><col width='100'><col width='250'>
            <tr> <th>AI-Acronym</th> <th>Owner</th> <th>Status</th> <th>Description</th> </tr>";

    for($i=0; $i<count($aiItems); $i++) {

        for($j=0; $j<count($aixml); $j++){

            if( $aixml->Actionitem[$j]->PID == trim( $aiItems[$i] ) &&
                       $aixml->Actionitem[$j]->GROUP == $selectedGroup)
            {

                echo "<tr> <td> <a href=?incident={$aiItems[$i]}&group={$selectedGroup}&aiacronym={$aixml->Actionitem[$j]->AIACRO}>".$aixml->Actionitem[$j]->AIACRO."</a> </td>";
                echo "<td>" .$aixml->Actionitem[$j]->OWNER. "</td>";

                for($q=0; $q<count($aireportxml); $q++){

                    if( $aireportxml->Aireport[$q]->AIID == trim( $aiItems[$i] ) ) {
                        if(isset($aireportxml->Aireport[$q]->STATUS)){
                            echo "<td>" .$aireportxml->Aireport[$q]->STATUS. "</td>";
                        }
                        else {
                            echo "<td>NULL</td>";
                        }
                    }

                }

                echo "<td>".substr($aixml->Actionitem[$j]->DESCRIPTION,0,25)."</td></tr>";
            }
        }
    }

    echo "</table>";
    //echo "<button>Add A New Action Item</button>";

}



function displayIncidentReportDetails($incidentNumber) {
    $incidentNumber = trim($_GET['incidentnumber']);
    $group = trim($_GET['groupname']);
    echo "<h2>".$group . "</h2><br/>";
    echo $incidentNumber . "<br/>";
    $aixml = simplexml_load_file(ACTIONITEMS) or die("Error: Cannot open Action Items file");
    for($i=0; $i<count($aixml); $i++) {
        if ($aixml->Actionitem[$i]->GROUP == $group && $aixml->Actionitem[$i]->PID == $incidentNumber) {
            echo "AIACRO: " .$aixml->Actionitem[$i]->AIACRO . "<br/>";
            echo "OWNER: " .$aixml->Actionitem[$i]->OWNER . "<br/>";
            $aireportxml = simplexml_load_file(AIREPORTS) or die("Error: Cannot open AIReports file");
            for($j=0; $j<count($aireportxml); $j++) {
                if ($aireportxml->Aireport[$j]->AIID == $incidentNumber) {
                    echo "DESCRIPTION: " . $aireportxml->Aireport[$j]->NDESCRIPTION . "<br/>";
                    echo "STATUS: " . $aireportxml->Aireport[$j]->STATUS . "<br/>";
                    echo "<br/><br/>";
                }
            }
        }
    }
}



function displayFullActionItem( $group, $acronym, $incident ) {
    $groupName = trim($_GET['group']);
    $aiacronym = trim($_GET['aiacronym']);
    $incidentNumber = trim($_GET['incident']);

    echo "<p>Action item for {$groupName} {$aiacronym}</p>";
    echo "<p>Add New Action Item | ";
    echo "Edit | ";
    echo "Report</p>";

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
?>