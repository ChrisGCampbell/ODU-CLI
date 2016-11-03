<?php
include 'header.html';
include 'functions.php';

if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       $group = trim($_POST['group']);
       $incidents = displayIncident($group);
       for($j=1; $j<count($incidents); $j++){
           echo '<button class="incidents">'.$incidents[$j].'</button><br/><br/>';
       }
   }
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
