<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    //For ease, this is where, when an admin selects user(s) to be removed, the site is directed to, and then directed back from. 
    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';
    //Error/bad data catching
    if(!isset($_SESSION["usr"]) || $_SESSION["usr"] != "admin"){
        header("Location: login.php");
    }
    elseif(!isset($_POST['userIdToDelete']) || (isset($_POST['userIdToDelete']) && $_POST['userIdToDelete'] == [])){
        resetMessages();
        $_SESSION["removeUserErrorMessage"] = "You must select a user to be removed!";
        header("Location: profile.php");
    }

    function sqlDeleteUser(){
        return "DELETE FROM registration
        WHERE id= :id";
    }
    function sqlDeleteUserStocks(){
        return "DELETE FROM userStock
        WHERE userId= :id";
    }
    //Removes the specified user from each of the tables, fully wiping them from the database.
    if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST['userIdToDelete'])){

        try{
            resetMessages();
            foreach($_POST['userIdToDelete'] as $userId){

                $pdo = getPDO();
                $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

                $pdoStatement = $pdo ->prepare(sqlDeleteUser());
                $pdoStatement -> bindParam(':id', $userId);
                $pdoStatement -> execute();

                $pdoStatement = $pdo ->prepare(sqlDeleteUserStocks());
                $pdoStatement -> bindParam(':id', $userId);
                $pdoStatement -> execute();

                if($pdoStatement){
                    $_SESSION["removeUserSuccessMessage"] = "User(s) and all attached records were removed successfully.";
                    $_SESSION["removeUserErrorMessage"] = "";
                }
            }
        }
        catch(PDOException $e){
            $e -> getMessage();
        }
        finally{
            $pdo = null;
        }

        header("Location: profile.php");
    }