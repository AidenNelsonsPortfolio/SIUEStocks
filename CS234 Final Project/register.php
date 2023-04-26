<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 25 2022
-->

<?php
    //This is the file for registering a user to the website.

    session_start();

    //Turning on Error Reporting
    error_reporting(E_ERROR);
    ini_set('display_errors', '1');

    include './Utils/functions.php';

    $returnMessage = "";

    function goodUser(){
        session_start();

        $_SESSION["usr"] = getValue("usr");
        header("Location: home.php");
    }

    //If the webpage is in post mode (after the user enters their information), then the page sends out a request to the included file to register that user.
    if($_SERVER["REQUEST_METHOD"]==="POST"){
        $returnMessage = registerUser(getValue("pwd"), getValue("cfmpwd"), getValue("usr"), getValue("email"));

        if ($returnMessage === ""){
            goodUser();
        }
    }
?>

<!DOCTYPE html>
<html lang="en" class='w3-camo-sandstone'>
    <head>
        <meta charset="UTF-8">
        <title>Register for StockWatchSIUE</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-camo.css">

        <link rel="shortcut icon" href="./Utils/favicon.ico" type="image/x-icon">
    </head>
    <body>
        <header class="w3-container w3-sand"><h1>Register for StockWatchSIUE</h1></header>

        <form class="w3-panel w3-border" action="<?php getPostback(); ?>" method="POST">
            <p>
                <input class="w3-input w3-border w3-sand" name="usr" placeholder="Username" required autofocus>
            </p>

            <p>
                <input class="w3-input w3-border w3-sand" type="password" name="pwd" placeholder="Password" required>
            </p>

            <p>
                <input class="w3-input w3-border w3-sand" type="password" name="cfmpwd" placeholder="Confirm Password" required>
            </p>

            <p>
                <input class="w3-input w3-border w3-sand" type='email' name="email" placeholder="Email (optional)">
            </p>

            <p>
                <button class="w3-button w3-green w3-round">Register</button>
            </p>

            <p style="color:red;">
                <?php echo $returnMessage;?>
            </p>

            <h2 class='w3-conatiner w3-panel w3-text-sand'>Already have an account? <span><a href="login.php" style="text-decoration:none;">Click <u>here</u></a></span></h2>

        </form>

    </body>

    <footer class="w3-panel w3-center w3-text-gray w3-small">
        &copy; <?php echo $year; ?> Aiden Nelson
    </footer>
</html>
