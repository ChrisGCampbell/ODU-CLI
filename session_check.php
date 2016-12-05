<?php

if(!isset($_SESSION['email'])){
    header('Location:sign_in.php');
    exit();
}
$userIsLoggedInWorkspace = Verify_Session_Email_Exists();
if ($userIsLoggedInWorkspace == false){
    header('Location:sign_in.php');
    exit();
}
include('echoSessionVariables.php');