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

function loadGroups() {
    $groupNames = [];
    $fileHandler = fopen(GROUPFILE, "r");
    $groupCount = fgets($fileHandler);

    for($i=0; $i<$groupCount; $i++) {
        $groupNames[$i] =  fgets($fileHandler);
    }
    populateGroupList($groupNames);
}

function populateGroupList($groupNames){
    for($i=0; $i<count($groupNames); $i++) {
        echo '<input type="radio" name="group" id="group" value="'.$groupNames[$i].'">'.$groupNames[$i];
    }
}

function displayIncident($groupName) {
    echo "<h2>" . $groupName. "</h2>";
    echo "<h3>Action Items for " . $groupName. "</h3>";
    $txtfile = file_get_contents(GROUPINCIDENTS);
    $rows    = explode("\n", $txtfile);

    for($i=0; $i<count($rows); $i++){
        if (strpos($rows[$i], $groupName) !== false) {
               $incidents = explode("|",$rows[$i]);
               break;
        }
    }
    $aixml = simplexml_load_file(ACTIONITEMS);
    echo "<table width='550'><col align='center' width='200'><col align='center' width='100'><col align='center' width='100'><col align='center' width='250'>
            <tr>
                <th>AI-Acronym</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Description</th>
             </tr>";

    for($j=0; $j<count($aixml); $j++){
        if($aixml->Actionitem[$j]->PID == trim($incidents[1])){
            echo "<tr><td>".$aixml->Actionitem[$j]->AIACRO."</td>";
            echo "<td>".$aixml->Actionitem[$j]->OWNER."</td>";
            echo "<td>".$aixml->Actionitem[$j]->STATUS."</td>";
            echo "<td>".substr($aixml->Actionitem[$j]->DESCRIPTION,0,25)."</td></tr>";
        }
    }
    echo "</table>";
    echo "<button>Add A New Action Item</button>";
}

function DisplayIncidentReportDetails($incidentNumber) {
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

function displayGroupList() {
    echo "<form id='groupList' method='POST' action='"; echo $_SERVER['PHP_SELF']; echo "'>";
    echo 'Select Group:';
    loadGroups();
    echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
}?>
