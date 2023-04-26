<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 25 2022
-->

<?php
    //This is the website for a pre-existing user to login to the application.

    session_start();

    //Turning on Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    function sqlSelectPWQuery(){
        return "SELECT pwd 
        FROM registration
        WHERE username=:usr;";
    }

    $errorMessage = "";
    $goodUser = false;
    
    //If the webpage is in post mode, check the input details.
    if($_SERVER["REQUEST_METHOD"]==="POST"){

        try{
            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlSelectPWQuery());
            $pdoStatement -> bindParam(':usr', getValue("usr"));
            $pdoStatement -> execute();
            
            //Executes and reads the pdoStatement results, then checks if the password_verify function returns that the hashed user's input password and the hash in the dbs are the same.
            foreach($pdoStatement as $row){
                if(!password_verify(getValue("pwd"), $row["pwd"])){
                    $errorMessage = "Incorrect password, please try again.";
                    break;
                }
                else{
                    $errorMessage = "Valid login information";
                    $goodUser = true;
                }
            }
            if($errorMessage == ""){
                $errorMessage = "The username is not set, please sign up for an account!";
            }
        }
        catch(PDOException $e){
            echo $e ->getMessage();
        }
        finally{
            $pdo = null;
        }
    }

    //If the user is logged in, then update that in the session array and redirect to their homepage.
    if($goodUser){
        $_SESSION["usr"] = getValue("usr");
        header("Location: home.php");
    }
    
?>


<!DOCTYPE html>
<html lang="en" class='w3-camo-sandstone'>
    <head>
        <meta charset="UTF-8">
        <title>Login to StockWatchSIUE</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-camo.css">

        <link rel="shortcut icon" href="./Utils/favicon.ico" type="image/x-icon">
    </head>
    <body>
        <header class="w3-container w3-sand"><h1>Login to StockWatchSIUE</h1></header>

        <form class="w3-panel w3-border" action="<?php getPostback(); ?>" method="POST">
            <p>
                <input class="w3-input w3-border w3-sand" name="usr" placeholder="Username" required autofocus>
            </p>

            <p>
                <input class="w3-input w3-border w3-sand" type="password" name="pwd" placeholder="Password" required>
            </p>

            <p>
                <button class="w3-button w3-green w3-round" >Log In</button>
            </p>
        </form>

        <p class="w3-red w3-large w3-center"><?php echo($errorMessage);?></p>

        <h2 class='w3-conatiner w3-panel w3-text-sand'>Don't have an account? <span><a href="register.php" style="text-decoration:none;">Click <u>here</u></a></span></h2>
    </body>
    <footer class="w3-panel w3-center w3-text-gray w3-small">
        &copy; <?php echo $year; ?> Aiden Nelson
    </footer>
</html>