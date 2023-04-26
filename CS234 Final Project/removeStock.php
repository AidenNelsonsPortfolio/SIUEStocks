<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    //Endpoint touched whenever the user selects stock(s) to remove.
    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    if(!isset($_SESSION["usr"]) ){
        header("Location: login.php");
    }
    elseif(!isset($_POST['stockToRemove']) || (isset($_POST['stockToRemove']) && $_POST['stockToRemove'] == [])){
        resetMessages();
        $_SESSION["removeStockErrorMessage"] = "You must select a stock to remove!";
        header("Location: profile.php");
    }

    function sqlDeleteStock(){
        return "DELETE FROM userStock
        WHERE userId = :uId
        AND stockId = :sId;";
    }

    //Removes each stock that a user specified using the above sql statement (or returns an error message if there are none selected)
    if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST['stockToRemove'])){

        try{
            resetMessages();            
            foreach($_POST['stockToRemove'] as $stock){
                $stockId = -1;
                $userId = -1;
                $badStock = true;

                $pdo = getPDO();
                $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

                $pdoStatement = $pdo ->prepare(sqlUserStockById());
                $pdoStatement -> bindParam(':username', getSessionValue("usr"));
                $pdoStatement -> bindParam(':ticker', $stock);
                $pdoStatement -> execute();
                //Double checks that the user's selected stock is one that they actually have.
                foreach($pdoStatement as $row){
                    if($row['ticker'] == $stock){
                        $_SESSION["removeStockErrorMessage"] = "";
                        $badStock = false;
                    }
                }
                if(!$badStock){
                    //Gets the stock id wanting to be deleted.
                    $pdoStatement = $pdo ->prepare(sqlStockId());
                    $pdoStatement -> bindParam(':ticker', $stock);
                    $pdoStatement -> execute();

                    foreach($pdoStatement as $row){
                        $stockId = $row['id'];
                    }
                    //Gets the user id
                    $pdoStatement = $pdo ->prepare(sqlUserId());
                    $pdoStatement -> bindParam(':usr', getSessionValue("usr"));
                    $pdoStatement -> execute();

                    foreach($pdoStatement as $row){
                        $userId = $row['id'];
                    }
                    //Now deletes the stock with the two above id's from join table.
                    $pdoStatement = $pdo ->prepare(sqlDeleteStock());
                    $pdoStatement -> bindParam(':uId', $userId);
                    $pdoStatement -> bindParam(':sId', $stockId);
                    $pdoStatement -> execute();
                    
                    if($pdoStatement){
                        $_SESSION["removeStockSuccessMessage"] = "Stock(s) successfully removed!";
                    }else{
                        $_SESSION["removeStockErrorMessage"] = "Something went wrong, please log out and try again!";
                    }
                }
            }
        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }
    }
    header("Location: profile.php");
?>