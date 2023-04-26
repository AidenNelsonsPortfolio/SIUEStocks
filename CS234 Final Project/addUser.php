<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    //This is the endpoint for adding a user to the website (only possible with admin account).

    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    if(!isset($_SESSION["usr"])){
        header("Location: login.php");
    }

    if($_SERVER["REQUEST_METHOD"]==="POST"){
        //Calls the registerUser function with passed data, and updates error message accordingly.
        resetMessages();
        $errorMessage = registerUser(getValue('pwd'), getValue('cfmpwd'), getValue('usr'), getValue('email'));
        if($errorMessage != ""){
            $_SESSION["addUserErrorMessage"] = $errorMessage;
        }
        else{
            $_SESSION["addUserSuccessMessage"] = "User added successfully!";
        }
    }
    header("Location:profile.php");
?>