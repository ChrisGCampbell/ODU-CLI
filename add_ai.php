<?php
include 'header.html';
include 'functions.php';


if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       $group = trim($_POST['group']);
       displayIncident($group);
   }
}
else {
    echo "<form id='groupList' method='POST' action='"; echo $_SERVER['PHP_SELF']; echo "'>";
    echo 'Select Group:';
    loadGroups();
    echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
}
?>


<?php include 'footer.html'; ?>
