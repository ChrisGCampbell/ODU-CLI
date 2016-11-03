<?php
/**
 * Created by PhpStorm.
 * User: Chris-Campbell
 * Date: 11/1/2016
 * Time: 7:19 PM
 */
define("GROUPFILE", "known_groups.txt");
define("GROUPINCIDENTS", "known_incidents.txt");

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
               for($j=1; $j<count($incidents); $j++){
                   echo $incidents[$j] . "<br>";
               }
            }
        }
}
?>