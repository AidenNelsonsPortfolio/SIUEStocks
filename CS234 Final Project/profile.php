<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php 

    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    if(!isset($_SESSION["usr"])){
        header("Location:login.php");
    }

    if($_SERVER["REQUEST_METHOD"]==="POST"){
        session_destroy();
        header("Location: login.php");
    }
    
    function sqlStocksToAdd(){
        return "SELECT ticker, name
        FROM stock
        ORDER BY ticker;";
    }

    function sqlGetUsersQuery(){
        return "SELECT id,username, email
        FROM registration
        WHERE username != 'admin'";
    }

    //Returns a multiselect form that shows all of the stocks that the user can add, triggers sql statement to add it to portfolio once they submit it.
    function getAddStockForm(){
        try{
            $form = "<form action='addStock.php' method='POST'> <select name='stockToAdd[]' multiple>". 
            "<optgroup class='w3-bar-block w3-card-4 w3-center' label='Add Multiple Using Ctrl.'>";
            
            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlStocksToAdd());
            $pdoStatement -> execute();

            foreach($pdoStatement as $row){
                $form .= "<option class='w3-bar-item w3-button' value=$row[ticker]> $row[name] ($row[ticker]) </option>";
            } 
            $form .= "</optgroup> </select> <button type='submit'>Add Stock(s) to Portfolio</button> </form>";

        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }
        return $form;
    }

    //Same as above (a multiselect form), but deletes each stock entry from the user's portfolio that they select.
    function getDeleteStockForm(){
        try{
            $form = "<form action='removeStock.php' method='POST'> <select name='stockToRemove[]' multiple >".
            "<optgroup class='w3-bar-block w3-card-4 w3-center' label='Remove Multiple Using Ctrl.'>";
            
            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlUserStocks());
            $pdoStatement -> bindParam(':username', getSessionValue("usr"));
            $pdoStatement -> execute();

            $i = 0;
            foreach($pdoStatement as $row){
                $form .= "<option class='w3-bar-item w3-button' value=$row[ticker]> $row[name] ($row[ticker]) </option>";
                $i += 1;
            } 
            $form .= "</optgroup> </select> <button type='submit'>Remove Stock(s) from Portfolio</button></form>";

            if($i == 0){
                $form = "<h3 class='w3-container w3-animate-top w3-text-sand w3-center'> You have no stocks to remove. Add some!</h3>";
            }

        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }

        return $form;
    }

    //Same as the stock one, just for users on the entire site, only visible to the admin user.
    function getDeleteUserForm(){
        try{
            $form = "<form action='removeUser.php' method='POST'> <select name='userIdToDelete[]' multiple>". 
            "<optgroup class='w3-bar-block w3-card-4 w3-text-black w3-center' label='Remove Multiple Using Ctrl.'>";
            
            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlGetUsersQuery());
            $pdoStatement -> execute();

            foreach($pdoStatement as $row){
                $form .= "<option class='w3-bar-item w3-button' value=$row[id]> $row[username]" . ($row['email'] != "" ? "(email: $row[email])" : " (No email provided)") . "</option>";
            } 
            $form .= "</optgroup> </select> <button type='submit' >Remove User(s) from Website (and delete all attached records)</button></form>";


        }catch(PDOException $e){
            echo $e ->getMessage();
        }finally{
            $pdo = null;
        }

        return $form;
    }

    //Exact same display as the register view that users get on the normal register page, just is for admins only to remotely add more users from their profile.
    function getAddUserForm(){
        return "<form class='w3-panel w3-border' action='addUser.php' method='POST'>
        <p>
            <input class='w3-input w3-border w3-sand' name='usr' placeholder='Username' required>
        </p>

        <p>
            <input class='w3-input w3-border w3-sand' type='password' name='pwd' placeholder='Password' required>
        </p>

        <p>
            <input class='w3-input w3-border w3-sand' type='password' name='cfmpwd' placeholder='Confirm Password' required>
        </p>

        <p>
            <input class='w3-input w3-border w3-sand' type='email' name='email' placeholder='Email (optional)'>
        </p>
        <p>
            <button class='w3-button w3-green w3-round'>Register</button>
        </p> </form>";
    }

    //If the user is an admin, then additional controls (like adding/removing users) are granted / made visible.
    function adminControl(){
        if($_SESSION['usr'] === "admin"){
            return "<p class='w3-container w3-animate-top' >". 
                "<h2 class='w3-sand w3-panel'>ADMIN CONTROL OF USERS BELOW</h2>" .
                "<br/>" . 
                "<h3 class='w3-container w3-text-black w3-sand w3-bottombar'>Remove User(s)</h3>" .
                getDeleteUserForm() . 
                "<span class='w3-red'>" . 
                getSessionValue('removeUserErrorMessage') . 
                "</span>" .
                "<span class='w3-green'>" . 
                getSessionValue('removeUserSuccessMessage') .
                "</span>" .
                "<br/>" . 
                "<br/>" . 
                "<h3 class='w3-container w3-text-black w3-sand w3-bottombar'>Add a User</h3>" .
                getAddUserForm() . 
                "<span class='w3-red'>" . 
                getSessionValue('addUserErrorMessage') . 
                "</span>" .
                "<span class='w3-green'>" . 
                getSessionValue('addUserSuccessMessage') .
                "</span>" .
                "</p>";
        }
        return "";
    }

    $name = getSessionValue("usr");
    $table = "";
    
    //Anytime the page is loaded, sql is triggered with the username to get the stocks of that user and display them in a formatted chart.
    try{

        $pdo = getPDO();
        $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        $pdoStatement = $pdo ->prepare(sqlUserStocks());
        $pdoStatement -> bindParam(":username", getSessionValue('usr'));
        $pdoStatement -> execute();

        $table = "<h2 class='w3-text-sand w3-container w3-animate-top'> User's Stock Table</h2><table class='w3-table w3-bordered w3-border w3-striped w3-sand w3-animate-top'><caption class='w3-large w3-sand'>". 
        "User Stocks Table</caption>" . 
        "<thead> <td>#</td> <td>Name</td> <td>Ticker</td></thead> <tbody>";
        
        $i = 1;
        foreach($pdoStatement as $row){
            $table .= "<tr> <td>$i</td> <td>$row[name]</td> <td>$row[ticker]</td></tr>";
            $i +=1;
        }
        $table .= "</tbody> </table>";

        if($i == 1){
            $table = "<h2 class='w3-text-sand w3-container w3-animate-top w3-center'>User's Stock Table</h2><h3 class='w3-container w3-animate-top w3-text-sand w3-center'>You have no stocks in your portfolio. Add some below!</h3>";
        }

    }catch(PDOException $e){
        echo $e->getMessage();
    }
    finally{
        $pdo = null;
    }
?>

<!DOCTYPE html>
<html lang="en" class="w3-camo-sandstone">
    <head>
        <meta charset="UTF-8">
        <title>StockWatchSIUE</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-camo.css">

        <link rel="shortcut icon" href="./Utils/favicon.ico" type="image/x-icon">
    </head>

    <?php echo(getHeader());?>

    <body>
        <div class="w3-panel w3-leftbar w3-rightbar w3-sand w3-container w3-animate-top">
            <h1 class="w3-xxxlarge w3-animate-top w3-center">Profile of <b><?php echo $name?></b> <?php echo getUserEmail($_SESSION["usr"]);?></h1>
        </div>

        <p class="w3-container">
            <?php echo($table); ?>
        </p>

        <?php echo(homeButton());?>

        <div class="w3-bottombar w3-animate-top" style="display:flex; vertical-align:middle; justify-content:space-around; align-items:center;">
            <div class="w3-animate-top w3-padding-32">
                <h2><b>Add Stock(s) From the S&P500 </b></h2>
                <?php echo(getAddStockForm());?>
                <span class='w3-red'> 
                    <?php echo(getSessionValue('addStockErrorMessage'));?>
                </span>
                <span class='w3-green'>
                    <?php echo(getSessionValue('addStockSuccessMessage'));?>
                </span>
            </div>

            <div class="w3-animate-top w3-padding-32">
                <h2> <b>Remove Stock(s) from Portfolio </b></h2>
                <?php echo(getDeleteStockForm());?>
                <span class='w3-red'> 
                    <?php echo(getSessionValue('removeStockErrorMessage'));?>
                </span>
                <span class='w3-green'>
                    <?php echo(getSessionValue('removeStockSuccessMessage'));?>
                </span>
            </div>
            <br/>
        </div>
        <div class="w3-container w3-panel w3-animate-top">    
            <?php echo adminControl();?>
        </div>  
        
        
    </body>
    <footer class="w3-panel w3-center w3-text-sand w3-small w3-animate-top">
        &copy; <?php echo $year; ?> Aiden Nelson
    </footer>
</html>