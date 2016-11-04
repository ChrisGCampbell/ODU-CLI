<?php
include 'header.html';
include 'functions.php';

if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       $group = trim($_POST['group']);
       $incidents = displayIncident($group);
       for($j=1; $j<count($incidents); $j++){
           echo '<a name="incidentLookup" href="add_ai.php?groupname='.$group.'&incidentnumber='.$incidents[$j].'">'.$incidents[$j].'</a><br/><br/>';
       }
   }
}
else if(isset($_GET['incidentnumber'])){
 $incidentNumber = $_GET['incidentnumber'];
 $group = $_GET['groupname'];
 echo $group . "<br/>";
 echo $incidentNumber . "<br/>";
}
else {
    echo "<form id='groupList' method='POST' action='"; echo $_SERVER['PHP_SELF']; echo "'>";
    echo 'Select Group:';
    loadGroups();
    echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
}
?>
<hr>
<div id="displayIncident"></div>
<br/>
<br/>
<br/>


<?php include 'footer.html'; ?>
