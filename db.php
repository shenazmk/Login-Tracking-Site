<?php
	$db_host = "localhost";

	// TODO 3: Update db.php with your MySQL account details
	$db_user = "smk868";  
	$db_pwd = "CS215_MySQL_24!"; 
	$db_db = "smk868";

	$charset = 'utf8mb4';
	$attr = "mysql:host=$db_host;dbname=$db_db;charset=$charset";
	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
?>