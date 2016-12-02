<?php
/**
 * Created by PhpStorm.
 * User: Chris-Campbell
 * Date: 12/2/2016
 * Time: 6:07 PM
 */
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