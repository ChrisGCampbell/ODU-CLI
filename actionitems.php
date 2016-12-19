<?php
session_start();

//require('Includes/session_check.php');
require('Includes/utilities.php');
include('header.html');

$actionitem = new ActionReports();
echo '<p id="AISection"></p>';


if ( empty($_POST) && empty($_GET) )

        echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->displayGroupSelectionList().'";</script>';

else {
        if ( array_key_exists( "submitGroup",$_POST ) ) {
            $_SESSION['pgroup'] = (trim($_POST['group']));
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->displayProjectIncidentList().'";</script>';
        }

        if ( array_key_exists( "pid",$_GET) && array_key_exists( "pgroup",$_GET ) ) {
            $_SESSION['pid'] = (trim($_GET['pid']));
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->displayActionItemLimitedView().'";</script>';
        }

        if ( array_key_exists( "editAI",$_GET ) ) {
            $_SESSION['aiacronym'] = $_GET['aiacronym'];
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->editAI().'";</script>';
        }

        if ( array_key_exists( "submitEditAI",$_POST ) ) {
            echo '<script>document.getElementById("AISection").innerHTML = "'. $actionitem->saveToFile($_POST['description'],$_POST['aiacronym'],$_POST['responsible'],$_POST['rationale'],$_POST['deadline']).'";</script>';
        }

        if( array_key_exists( "newActionItem", $_POST ) ) {
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->CreateNewActionItem($_POST['pid'], $_POST['pgroup']).'";</script>';
        }

        if ( array_key_exists( "submitAddedNewActionItem", $_POST ) ) {
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->addNewAIToFile().'";</script>';
        }

        if( array_key_exists( "viewAI", $_GET ) ) {
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->viewAI().'";</script>';
        }

        if( array_key_exists( "reportAI", $_GET )) {
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->addAiReportForm($_GET['aiacronym']).'";</script>';
        }

        if ( array_key_exists( "submitNewReport", $_POST ) ) {
            echo '<script>document.getElementById("AISection").innerHTML = "'.$actionitem->addNewReportToFile().'";</script>';
        }

}


include('footer.html');