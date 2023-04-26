<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    //Endpoint for ending the session of a user, redirect to login

    session_start();
    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    if(!isset($_SESSION["usr"])){
        header("Location: login.php");
    }

    if($_SERVER["REQUEST_METHOD"]==="POST"){
        session_destroy();
        header("Location: login.php");
    }
?>