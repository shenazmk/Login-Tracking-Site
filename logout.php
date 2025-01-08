<?php
session_start();

// delete all of the session variables
session_unset();
session_destroy();

// redirect the user back to the login page
header("Location: login.php");
exit();
