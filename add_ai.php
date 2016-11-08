<?php
include 'header.html';
include 'functions.php';

if(isset($_POST['submitGroup'])) {
   if (isset($_POST['group'])) {
      displayIncident(trim($_POST['group']));
    }
}

else if(isset($_GET['incidentnumber'])){
    displayIncidentReportDetails(trim($_GET['incidentnumber']));
}

else { ?>

    <form id="groupList" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        Select Group:
        <?php
        $groupNames = loadGroups();

        for($i=0; $i<count($groupNames); $i++) {
            echo '<input type="radio" name="group" id="group" value="'.$groupNames[$i].'">'.$groupNames[$i];
        }
        ?>
        &nbsp<input type="submit" value="submit" name="submitGroup"></form>
    </form>
<?php } ?>


<hr><div id="displayIncident"></div>


<?php include 'footer.html'; ?>
