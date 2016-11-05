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

function DisplayIncidentDetails($incidentNumber) {
    $incidentNumber = $_GET['incidentnumber'];
    $group = $_GET['groupname'];
    echo "<h2>".$group . "</h2><br/>";
    echo $incidentNumber . "<br/>";
    $xml = simplexml_load_file(ACTIONITEMS);
}

function displayGroupList() {
    echo "<form id='groupList' method='POST' action='"; echo $_SERVER['PHP_SELF']; echo "'>";
    echo 'Select Group:';
    loadGroups();
    echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
}?>
