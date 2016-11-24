<?php
include 'header.html';
include 'functions.php';


if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       displayProjectIncidents(trim($_POST['group']));
   }
}


else if(isset($_GET['pid']) && isset($_GET['pgroup'])){
    displayActionItemDetails(trim($_GET['pid']), trim($_GET['pgroup']));
}


elseif( isset($_GET['group']) && isset($_GET['aiacronym']) && isset($_GET['incident'])) {
    displayFullActionItem( $_GET['group'] , $_GET['aiacronym'], $_GET['incident'] );
}


elseif( isset($_GET['editAI']) && isset($_GET['aiacronym'])) {
    editAI($_GET['aiacronym']);
}


elseif( isset($_POST['submitEditAI'])) {
    saveToFile($_POST['description'],$_POST['aiacronym']);
}

elseif( isset($_POST['newActionItem'])) {
    newActionItemForm($_POST['newActionItem']);
}


elseif( isset($_GET['newActionItem'])) {
    newActionItemForm($_GET['submitNewActionItem']);
}

elseif ( isset($_POST['submitAddedNewActionItem'])) {
    addNewAIToFile();
}
else {
    displayGroupOptions();
}


include 'footer.html';
?>


