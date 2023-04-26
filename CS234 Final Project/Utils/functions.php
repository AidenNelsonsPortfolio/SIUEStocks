<!--
    #Author: Aiden Nelson
    #Date of Start: Nov. 24 2022
    #Last Modified: Nov. 26 2022
-->

<?php
    //This file is never accessed in browsing, but just holds a list of common functions to be shared among files.

    error_reporting(E_ERROR);
    ini_set('display_errors', '1');

    $year = date('Y');

    function getPostback(){
        return $_SERVER["PHP_SELF"];
    }

    function getValue($key){
        if(isset($key)){
            return htmlspecialchars(trim($_POST[$key]));
        }
        return "";
    }

    //Function for connection string (to mysql database)
    function getDSN(){
        $host = "localhost";
        $dbname = "project";
        $port = 8889;
        return "mysql:host=$host;dbname=$dbname;port=$port;";
    }

    function getUsername(){
        return "root";
    }

    function getPassword(){
        return "root";
    }

    function getPDO(){
        return new PDO(getDSN(), getUsername(), getPassword());
    }

    function getSessionValue($key){

        if(isset($_SESSION[$key])){
            return $_SESSION[$key];
        }

        return "";
    }
    //Function to return the header that is seem across the entire site.
    function getHeader(){
        return "<div class='w3-bar w3-border w3-sand w3-animate-left'>
        <a href='home.php' class='w3-bar-item w3-button w3-hover-none w3-xxlarge' title='Home Page'>StockWatchSIUE</a>
        <form action='signOut.php' method='POST'>
            <input class='w3-button w3-border w3-border-red w3-round-large w3-right w3-section w3-margin-right w3-hover-red' type='submit' value='Sign Out'/>
        </form>
        <a href='profile.php' class='w3-bar-item w3-button w3-border w3-border-black w3-hover-light-gray w3-right w3-section w3-round-large w3-margin-right'>View Profile/Edit Portfolio</a>
        </div>";
    }
    //HTML for home button
    function homeButton(){
        return "<div class='w3-container w3-center w3-animate-top'><button class='w3-border w3-border-black w3-button w3-sand w3-round-large'><a style='display:flex; align-items:center; justify-content:center; text-decoration:none;' href='home.php' class='w3-animate-top w3-container w3-padding-16'>View Live Stock Prices</a></button></div>";
    }

    //Below are most of the commonly used sql statements across the entire site.
    function sqlUserStocks(){
        
        return "SELECT s.ticker, s.id, s.name
        FROM registration as r, userStock as u, stock as s
        WHERE r.id = u.userId
        AND u.stockId = s.id
        AND r.username = :username
        ORDER BY ticker;";
    }

    function sqlUserStockById(){
        return "SELECT s.ticker, s.id, s.name
        FROM registration as r, userStock as u, stock as s
        WHERE r.id = u.userId
        AND u.stockId = s.id
        AND r.username = :username
        AND s.ticker = :ticker;";
    }

    function sqlUserID(){
        return "SELECT id
        FROM registration
        WHERE username=:usr;";
    }

    function sqlStockID(){
        return "SELECT id
        FROM stock
        WHERE ticker=:ticker;";
    }  
    //Resets all of the error/success messages so they are not redundantly displayed.
    function resetMessages(){
        $_SESSION["addStockErrorMessage"] = "";
        $_SESSION["addStockSuccessMessage"] = "";
        $_SESSION["removeStockErrorMessage"] = "";
        $_SESSION["removeStockSuccessMessage"] = "";
        $_SESSION["addUserErrorMessage"] = "";
        $_SESSION["addUserSuccessMessage"] = "";
        $_SESSION["removeUserErrorMessage"] = "";
        $_SESSION["removeUserSuccessMessage"] = "";
    }

    function sqlSelectQuery(){
        return "SELECT id 
        FROM registration
        WHERE username=:usr;";
    }

    function sqlInsertPersonWithEmail(){
        return "INSERT INTO registration (username, pwd, email)
        VALUES (:usr, :pwd, :email);";
    }
    
    function sqlInsertPersonNoEmail(){
        return "INSERT INTO registration (username, pwd)
        VALUES (:usr, :pwd);";
    }

    //Checks the values passed in for pwd, the user's confirm password field, the username (that it is not already taken), and their email (I do nothing with it, really).
    function registerUser($pwd, $cfmpwd, $usr, $email){
        //Makes sure that the pwd is greater than 5 characters and that the two entered ones match
        if($pwd != $cfmpwd || $pwd == "" || strlen($pwd) < 5){
            return "Your passwords do not match, or they are shorter than 5 characters. Please try again.";
        }

        else{
            try{
                $goodUsername = true;
                $pdo = getPDO();
                $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

                //Try to get the username from the dbs
                $pdoStatement = $pdo ->prepare(sqlSelectQuery());
                $pdoStatement -> bindParam(':usr', $usr);
                $pdoStatement -> execute();
                
                foreach($pdoStatement as $row){
                    if($row != "" && $row != null && count($row) != 0){
                        $goodUsername = false;
                    }
                }
                //Will catch and return message only if prior sql statement returned user.
                if(!$goodUsername){
                    return "Your username is already in use, please select another one!";
                }
                else{
                    //Now will register the user using password hashing, and, if email is provided, email regex authentication.

                    if($email != ""){
                        //Checks the email via regex provided by textbook resources.
                        $regex = "/\A[a-z0-9!#$%&'*+\=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\z/";
                        if(preg_match($regex, $email)){
                            //If the above holds, then the user is registered, along with their email being saved.
                            $pdoStatement = $pdo ->prepare(sqlInsertPersonWithEmail());
                            $pdoStatement -> bindParam(':usr',$usr);
                            $pdoStatement -> bindParam(':pwd', password_hash($pwd, PASSWORD_BCRYPT));
                            $pdoStatement -> bindParam(':email', $email);
                            $pdoStatement -> execute();
                            return "";
                        }
                        else{
                            return "<h2 class='w3-red w3-border'>Your email is not in a valid format, please enter it again!</h2>";
                        }
                    }
                    else{
                        //If no email was provided, inserts user without email.
                        $pdoStatement = $pdo ->prepare(sqlInsertPersonNoEmail());
                        $pdoStatement -> bindParam(':usr',$usr);
                        $pdoStatement -> bindParam(':pwd', password_hash($pwd, PASSWORD_BCRYPT));
                        $pdoStatement -> execute();

                        return "";
                    }
                    
                }
            }
            catch(PDOException $e){
                echo $e -> getMessage();
            }finally{
                $pdo = null;
            }
        }
    }

    function sqlGetUserEmail(){
        return "SELECT email
        FROM registration
        WHERE username=:usr;";
    }
    //Just gets the email (if present) of a given user by the above sql statement.
    function getUserEmail($usr){
        try{
            $email = "";

            $pdo = getPDO();
            $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            $pdoStatement = $pdo ->prepare(sqlGetUserEmail());
            $pdoStatement -> bindParam(':usr', $usr);
            $pdoStatement -> execute();

            foreach($pdoStatement as $row){
                $email = " (email: " . $row["email"] . ")";
            }

            return ($email != " (email: )")? $email : " (No contact email)";

        }
        catch(PDOException $e){
            $e -> getMessage();
        }
        finally{
            $pdo = null;
        }
        
    }

?>