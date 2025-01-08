<?php
require_once("db.php");

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); //encodes
    return $data;
}


// Check whether the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errors = array();
    $dataOK = TRUE;
    
    // Get and validate the username and password fields
    $username = test_input($_POST["username"]);
    $unameRegex = "/^[a-zA-Z0-9_]+$/";
    if (!preg_match($unameRegex, $username)) {
        $errors["username"] = "Invalid Username";
        $dataOK = FALSE;
    }

    $password = test_input($_POST["password"]);
    $passwordRegex = "/^.{8}$/";
    if (!preg_match($passwordRegex, $password)) {
        $errors["password"] = "Invalid Password";
        $dataOK = FALSE;
    }

    // Check whether the fields are not empty
    if ($dataOK) {

        // Connect to the database and verify the connection
        try {
            $db = new PDO($attr, $db_user, $db_pwd, $options);

            // Query to check whether the user's credentials are in the database i.e. the user is logging in with the correct credentials.
            $query = "SELECT user_id, first_name, last_name, avatar_url FROM Loggers WHERE username = '$username' AND password = '$password'";
            $result = $db->query($query);

            if (!$result) {
                // handle errors
                $errors["Database Error"] = "Could not retrieve user information";
            } elseif ($row = $result->fetch()) {
                // If there's a row, we have a match and login is successful!

                session_start();

                $user_id = $row["user_id"];
                $_SESSION["user_id"] = $user_id;
                $_SESSION["first_name"] = $row["first_name"];
                $_SESSION["last_name"] = $row["last_name"];
                $_SESSION["avatar_url"] = $row["avatar_url"];

                // Save the IP address of the logged in user.
                $ip = $_SERVER['REMOTE_ADDR'];

                // Query to save the fact that the user logged in
                $query = "INSERT INTO Logins (user_id, login_time, ip_address) VALUES ('$user_id', NOW(), '$ip')";

                if ($db->exec($query)) {
                    // Close the database connection
                    $db = null;

                    // Redirect the user to loginHistory page.
                    header("Location: loginHistory.php");
                    exit();
                } else {
                    $errors["Database"] = "Error storing login data.";
                }
            } else {
                // login unsuccessful
                $errors["Login Failed"] = "That username/password combination does not exist.";
            }

            $db = null;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }

    } 

    foreach($errors as $message) {
        echo $message . "<br />\n";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>CS215 Homepage</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script src="js/eventHandlers.js"></script>
</head>

<body>
    <div id="container">
        <header id="header-auth">
            <h1>My Login History</h1>
        </header>
        <div id="main-left">

        </div>
        <main id="main-center">
            <form action="" method="post" class="auth-form" id="login-form">
                <p class="input-field">
                    <label>Username</label>
                    <input type="text" id="username" name="username" />
                <p id="error-text-username" class="error-text hidden">Username is invalid</p>
                </p>
                <p class="input-field">
                    <label>Password</label>
                    <input type="password" id="password" name="password" />
                <p id="error-text-password" class="error-text hidden">Password is invalid</p>
                </p>
                <p class="input-field">
                    <input type="submit" class="form-submit" value="Login" />
                </p><br>
                <div id="last-login"></div>
            </form>
            <div class="foot-note">
                <p>Don't have an account? <a href="signup.php">Signup</a></p>
            </div>
        </main>
        <div id="main-right">

        </div>
        <footer id="footer-auth">
            <p class="footer-text">CS 215: Lab 11 Template</p>
        </footer>
    </div>
    <script src="js/eventRegisterLogin.js"></script>
</body>

</html>