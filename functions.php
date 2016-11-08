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
    return $groupNames;
}

function displayIncident($groupName) {
    echo "<h2>" . $groupName. "</h2>";
    $txtfile = file_get_contents(GROUPINCIDENTS);
    $rows    = explode("\n", $txtfile);

    for($i=0; $i<count($rows); $i++){
        if (strpos($rows[$i], $groupName) !== false) {
               $incidents = explode("|",$rows[$i]);
               break;
        }
    }
    for($j=1; $j<count($incidents); $j++){
        echo '<a name="incidentLookup" href="add_ai.php?groupname='.$groupName.'&incidentnumber='.$incidents[$j].'">'.$incidents[$j].'</a><br/><br/>';
    }
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

