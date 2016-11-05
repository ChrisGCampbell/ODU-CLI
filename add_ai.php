<?php
include 'header.html';
include 'functions.php';

if(isset($_POST['submitGroup'])){
   if(isset($_POST['group'])){
       displayIncident(trim($_POST['group']));
   }
} else if(isset($_GET['incidentnumber'])){
    displayIncidentReportDetails(trim($_GET['incidentnumber']));
} else {
    displayGroupList();
}?>


<hr><div id="displayIncident"></div>

<?php include 'footer.html'; ?>
