<?php
require_once("db.php");

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); //encodes
    return $data;
}

// This variables will keep track of errors and form values
// we find while processing the form but we'll make them global
// so we can display POST results on the form when there's an error.
$errors = array();
$firstName = "";
$lastName = "";
$username = "";
$password = "";
$dob = "";

    // Check whether the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If we got here through a POST submitted form, process the form

    // Collect and validate form inputs
    $firstName = test_input($_POST["fname"]);
    $lastName = test_input($_POST["lname"]);
    $username = test_input($_POST["username"]);
    $password = test_input($_POST["password"]);
    $dob = test_input($_POST["dob"]);;
    
    // Form Field Regular Expressions
    $nameRegex = "/^[a-zA-Z]+$/";
    $unameRegex = "/^[a-zA-Z0-9_]+$/";
    $passwordRegex = "/^.{8}$/";
    $dobRegex = "/^\d{4}[-]\d{2}[-]\d{2}$/";
    
    // Validate the form inputs against their Regexes 
    if (!preg_match($nameRegex, $firstName)) {
        $errors["fname"] = "Invalid First Name";
    }
    if (!preg_match($nameRegex, $lastName)) {
        $errors["lname"] = "Invalid Last Name";
    }
    if (!preg_match($unameRegex, $username)) {
        $errors["username"] = "Invalid Username";
    }
    if (!preg_match($passwordRegex, $password)) {
        $errors["password"] = "Invalid Password";
    }
    if (!preg_match($dobRegex, $dob)) {
        $errors["dob"] = "Invalid DOB";
    }

    // Declare $target_file here so we can use it later
    $target_file = "";
    // try to make a MySQL connection
    // catch and report any  errors
    try {
        $db = new PDO($attr, $db_user, $db_pwd, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }


    // write a SQL query that finds matches for the username in the Loggers table
    $query = "SELECT username FROM Loggers WHERE username='$username'";

    // query() to run the query
    $result = $db->query($query);

    // fetch() to get the value of the first result of the query
    $match = $result->fetch();

    // If that email address is already taken
    if ($match) {
        $errors["Account Taken"] = "A user with that username already exists.";
    }
    
    //If there are no errors so far we can try inserting a user
    if (empty($errors)) {

        // exec() with an INSERT INTO statement to store the user's details in the Loggers table.
        // Hint: Use "avatar_stub" as a temporary value for avatar_url.
        $query = "INSERT INTO Loggers (first_name, last_name, username, password, dob, avatar_url) VALUES ('$firstName', '$lastName', '$username', '$password', '$dob', 'avatar_stub')";
        $result = $db->exec($query);

        if (!$result) {
            $errors["Database Error:"] = "Failed to insert user";
        } else {
            // Directory where the avatars will be uploaded.
            $target_dir = "uploads/";
            $uploadOk = TRUE;
        
            // Fetch the image filetype
            $imageFileType = strtolower(pathinfo($_FILES["profilephoto"]["name"],PATHINFO_EXTENSION));

            // Use lastInsertId() to get the user_id from the last insert query.
            $user_id = $db->lastInsertId();
            
            // Set the target file name nased on user_id
            $target_file = "uploads/$user_id.$imageFileType";

            // Check whether the file exists in the uploads directory
            if (file_exists($target_file)) {
                $errors["profilephoto"] = "Sorry, file already exists. ";
                $uploadOk = FALSE;
            }
                
            // Check whether the file is not too large
            if ($_FILES["profilephoto"]["size"] > 1000000) {
                $errors["profilephoto"] = "File is too large. Maximum 1MB. ";
                $uploadOk = FALSE;
            }

            // Check image file type
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                $errors["profilephoto"] = "Bad image type. Only JPG, JPEG, PNG & GIF files are allowed. ";
                $uploadOk = FALSE;
            }

            // Check if $uploadOk still TRUE after validations
            if ($uploadOk) {
                // Move the user's avatar to the uploads directory and capture the result as $fileStatus.
                $fileStatus = move_uploaded_file($_FILES["profilephoto"]["tmp_name"], $target_file);

                // Check $fileStatus:
                if (!$fileStatus) {
                    // The user's avatar file could not be moved
                    // add a suitable error message to errors array be displayed on the page
                    $errors["Server Error"] = "File could not be uploaded. Contact system administrator.";
                    $uploadOk = FALSE;
                }
            }
            
            // Check if $uploadOk still TRUE after attempt to move
            if (!$uploadOk)
            {
                // exec() with a DELETE FROM statement to remove the temporary user record
                $query = "DELETE FROM Loggers WHERE user_id='$user_id'";
                $result = $db->exec($query);
                if (!$result) {
                    $errors["Database Error"] = "could not delete user when avatar upload failed";
                }
                $db = null;
            } else {
                // exec() to UPDATE the user's avatar_url field 
                // in the Loggers table so that we can easily use it.
                $query =  "UPDATE Loggers SET avatar_url='$target_file' WHERE user_id='$user_id'";
                $result = $db->exec($query);
                if (!$result) {
                    $errors["Database Error:"] = "could not update avatar_url";
                } else {
                    // close the database connection
                    $db = null;
                    
                    // redirect the user to the login page
                    header("Location: login.php");
            
                    // exit the php script
                    exit();

                }
            }// image was uploadOk
        } // Insert user query worked
    } 
    

    if (!empty($errors)) {
        foreach($errors as $type => $message) {
            print("$type: $message \n<br />");
        }
    }

} // submit method was POST
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
        <main id="main-left">

        </main>
        <main id="main-center">
            <form action="" method="post" class="auth-form" id="signup-form" enctype="multipart/form-data">
                <p class="input-field">
                    <label>First Name</label>
                    <input type="text" id="fname" name="fname" value="<?= $firstName ?>" /></p>
                <p id="error-text-fname" class="error-text <?= isset($errors['fname'])?'':'hidden' ?>">First name is invalid</p>

                <p class="input-field">
                    <label>Last Name</label>
                    <input type="text" id="lname" name="lname" value="<?= $lastName ?>" /> </p>
                <p id="error-text-lname" class="error-text <?= isset($errors['lname'])?'':'hidden' ?>">Last name is invalid</p>

                <p class="input-field">
                    <label>Username</label>
                    <input type="text" id="username" name="username" value="<?= $username ?>" /></p>
                <p id="error-text-username" class="error-text <?= isset($errors['username'])?'':'hidden' ?>">Username is invalid</p>

                <p class="input-field">
                    <label>Password</label>
                    <input type="password" id="password" name="password" value="<?= $password ?>" /></p>
                <p id="error-text-password" class="error-text <?= isset($errors['password'])?'':'hidden' ?>">Password is invalid</p>

                <p class="input-field">
                    <label>Confirm Password</label>
                    <input type="password" id="cpassword" name="cpassword" /></p>
                <p id="error-text-cpassword" class="error-text hidden">Confirm password is invalid</p>

                <p class="input-field">
                    <label>Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?= $dob ?>" /></p>
                <p id="error-text-dob" class="error-text <?= isset($errors['dob'])?'':'hidden' ?>">Date of birth is invalid</p>

                <p class="input-field">
                    <label>Profile Photo</label>
                    <input type="file" id="profilephoto" name="profilephoto" accept="image/*" /></p>
                <p id="error-text-profilephoto" class="error-text <?= isset($errors['profilephoto'])?'':'hidden' ?>">Profile photo is invalid</p>


                <p>
                    <input type="submit" class="form-submit" value="Signup" />
                </p><br />
            </form>
            <div class="foot-note">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </main>
        <main id="main-right">

        </main>
        <footer id="footer-auth">
            <p class="footer-text">CS 215: Lab 3 Solution</p>
        </footer>
    </div>
    <script src="js/eventRegisterSignup.js"></script>
</body>

</html>