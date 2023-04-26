<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    //PHP endpoint for functionality allowing users to add multiple stocks to their portfolio from the SP500 list.
    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    //Catches error if the user did not send over a proper stockToAdd request.
    if(!isset($_SESSION["usr"])){
        header("Location: login.php");
    }
    elseif(!isset($_POST['stockToAdd']) || (isset($_POST['stockToAdd']) && $_POST['stockToAdd'] == [])){
        resetMessages();
        $_SESSION["addStockErrorMessage"] = "You must select a stock to add!";
        header("Location: profile.php");
    }

    function sqlInsertStock(){
        return "INSERT INTO userStock (userId, stockId) VALUES
        (:uId, :sId);";
    }

    if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST['stockToAdd'])){
        try{
            resetMessages();
            //B/C it is an array (the stockToAdd key in post), cycle through each
            foreach($_POST['stockToAdd'] as $stock){

                $stockId = -1;
                $userId = -1;
                $badStock = false;

                $pdo = getPDO();
                $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

                $pdoStatement = $pdo ->prepare(sqlUserStockById());
                $pdoStatement -> bindParam(':username', getSessionValue("usr"));
                $pdoStatement -> bindParam(':ticker', $stock);
                $pdoStatement -> execute();
                //Ensures that the stock is not already present in the user's portfolio, if it is, return error message.
                foreach($pdoStatement as $row){
                    if($row['ticker'] == $stock){
                        $_SESSION["addStockErrorMessage"] = "The stock ($row[ticker]) had already been added to your portfolio, please pick a different one!";
                        $badStock = true;
                    }
                }
                if(!$badStock){

                    //Otherwise, get the stock and user id below
                    $pdoStatement = $pdo ->prepare(sqlStockId());
                    $pdoStatement -> bindParam(':ticker', $stock);
                    $pdoStatement -> execute();

                    foreach($pdoStatement as $row){
                        $stockId = $row['id'];
                    }

                    $pdoStatement = $pdo ->prepare(sqlUserId());
                    $pdoStatement -> bindParam(':usr', getSessionValue("usr"));
                    $pdoStatement -> execute();

                    foreach($pdoStatement as $row){
                        $userId = $row['id'];
                    }

                    //Then add that stock entry via below sql statement (and the id's grabbed above)
                    $pdoStatement = $pdo ->prepare(sqlInsertStock());
                    $pdoStatement -> bindParam(':uId', $userId);
                    $pdoStatement -> bindParam(':sId', $stockId);
                    $pdoStatement -> execute();

                    if($pdoStatement){
                        $_SESSION["addStockSuccessMessage"] = "Stock(s) successfully added!";
                    }else{
                        $_SESSION["addStockErrorMessage"] = "Something went wrong, please log out and try again!";
                    }
                }
            }

        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }
    }
    header("Location:profile.php");
?>