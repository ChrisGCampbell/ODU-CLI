<?php
include 'header.html';
include 'functions.php';

if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       displayListOfAIS(trim($_POST['group']));
   }
}


else if(isset($_GET['incidentnumber'])){
    displayIncidentReportDetails(trim($_GET['incidentnumber']));
}


elseif( isset($_GET['group']) && isset($_GET['aiacronym']) && isset($_GET['incident'])) {
    displayFullActionItem( $_GET['group'] , $_GET['aiacronym'], $_GET['incident'] );
}


else {
    displayGroupOptions();
}



include 'footer.html';
?>


