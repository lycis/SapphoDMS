<?php
	session_start();
	
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	if(!isset($_POST["username"]) || !isset($_POST["password"])) die("NOK;Please fill out the whole form!");
	
	if(!preg_match("/^[A-Za-z0-9_\.\-]{3,30}$/", $_POST["username"])) die("NOK;The username contains invalid characters!");
	
	if($sdbc->insert('user', array('user_name' => $_POST["username"],
	                                'user_password' => crypt($_POST["password"]))))
		die("NOK;There was an error in the server request: ".mysql_error());
	echo "OK;Everything is fine :)";
	$sdbc->close();
?>
	