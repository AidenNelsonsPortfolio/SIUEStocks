<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 
    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    $stockMessage = "";
    $errorMessage = "";

    if(!isset($_SESSION["usr"])){
        header("Location: login.php");
    }

    //Please don't reveal these :)  (it is a free api, though)
    const APIKEY ='**************************************';
    const APIHOST ='apidojo-yahoo-finance-v1.p.rapidapi.com';


    //generic sql statement
    function sqlGetStockInfoQuery(){
        return "SELECT name, ticker, sector, subIndustry, headquarters, foundedDate
        FROM stock as s
        WHERE s.ticker=:ticker;";
    }

    //This function gets all of the stocks that a user has in their account, displays them in a multiple select
    function getStocks(){
        $dropdown = "<div class='w3-rightbar w3-bottombar w3-margin-left w3-container' style='display:flex; justify-content:space-around;'> " . 
            "<label class='w3-sand w3-text-black w3-label w3-center w3-margin-bottom w3-large w3-padding-16' for='ticker' style='align-self:center;'> Select Some of Your Stocks for Prices! </label>" .
            "<select name='ticker[]' class='w3-margin-right w3-margin-left' style='width:50%; text-align:center;' multiple>";
        $dropdown .= "<optgroup class='w3-bar-block w3-card-4' label='Select Multiple Using Ctrl!'>";
        try{
            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlUserStocks());
            $pdoStatement -> bindParam(':username', getSessionValue("usr"));
            $pdoStatement -> execute();
            
            $i = 0;
            foreach($pdoStatement as $row){
                $selected = getSessionValue('ticker') == $row['ticker']?"selected":"";
                $dropdown .= "<option class='w3-bar-item w3-button w3-center' value=$row[ticker] $selected> $row[name] ($row[ticker]) </option>";
                $i += 1;
            }

            $dropdown .= "</optgroup>" . "</select> <button type='submit' class='w3-button w3-border w3-border-black w3-green w3-round-large w3-margin-left'style='align-self:center;'>Click Here After Selection </button></div>";

            if($i == 0){
                return "<h2 class='w3-animate-top w3-center'>You don't have any stocks yet!</h2>";
            }

        }catch(PDOException $e){
            echo $e->getMessage();
        }
        finally{
            $pdo = null;
        }
        return $dropdown;

    }

    $stockTable = "";

    //Build the table once a user selects stock(s) to show prices of.
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ticker"])){
        $errorMessage = "";
        try{
            $_SESSION["ticker"] = $_POST["ticker"];

            $stockTable = "<table class='w3-table w3-bordered w3-border w3-striped w3-sand w3-animate-top'><caption class='w3-large w3-sand'>". 
            "Selected Stock Table</caption>" . 
            "<thead> <td>Ticker</td> <td>Short Name</td> <td>Current Price</td> <td>Previous Close</td> <td>Net Change (%)</td> <td>Daily Volume</td> </thead> <tbody id='stockTableBody'>";
            
            foreach($_POST['ticker'] as $ticker){

                $stockTable .= "<tr id='$ticker'>";

                $stockTable .= "<script>
                getData('". $ticker . "', '". APIKEY . "','" . APIHOST . "');
                </script>";

                $stockTable .= "</tr>";
            }

            $stockTable .= "</tbody> </table>";

        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }
    }
    //Catch if there are no selected stocks
    elseif($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_POST["ticker"]) ||(isset($_POST["ticker"]) && $_POST["ticker"] == []))){
        $errorMessage = "<h4 class='w3-red w3-center'>You must select one or more stocks to be able to see its/their price(s)!</h4>";
    }
    
?>

<!DOCTYPE html>
<html lang="en" class="w3-camo-sandstone">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-camo.css">
        <link rel="shortcut icon" href="./Utils/favicon.ico" type="image/x-icon">

        <script src='./Utils/getStockData.js' type='text/javascript'></script>
    </head>
    <body>
        <title>StockWatchSIUE</title>

        <?php echo(getHeader());?>

        <div class="w3-container w3-panel w3-sand w3-animate-top"><h1 class="w3-xxxlarge w3-center">Welcome to StockWatchSIUE</h1></div>

        <div class="w3-container w3-panel w3-sand w3-animate-top"><h1 class="w3-xlarge w3-center">Select Your Stocks Below to See Live Prices and Details!</h1></div>

        <form action="<?php getPostback(); ?>" method="POST" class='w3-container w3-animate-top w3-camo-sandstone'>
            <?php echo getStocks();?>
        </form>

        <div class='w3-container w3-animate-top w3-camo-sandstone w3-center'>
            <?php echo $errorMessage;?>
        </div>

        <div id="stockData" class='w3-container w3-panel w3-padding'>
            <?php echo $stockTable;?>
        </div>

        <div class='w3-center w3-container w3-animate-top'>
            <button class='w3-border w3-border-black w3-button w3-sand w3-round-large'>
                <a style='display:flex; align-items:center; justify-content:center; text-decoration:none;' href='profile.php' class='w3-animate-top w3-container w3-padding-16'>Find Stocks For Portfolio
                </a>
            </button>
        </div>
    </body>

    <footer class="w3-panel w3-center w3-text-sand w3-small w3-animate-top">
        &copy; <?php echo $year; ?> Aiden Nelson
    </footer>
</html>
