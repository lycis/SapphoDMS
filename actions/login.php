<?php
	session_start();
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	if(!isset($_POST["username"]) || !isset($_POST["password"])) die("NOK");
	
	if(!preg_match("/^[A-Za-z0-9_\.\-]{3,30}$/", $_POST["username"])) die("NOK");
	
	$where = "user_name = '".$_POST["username"]."'";
	if($sdbc->select('user', '*', $where)) 
		die("NOK;Could not get userdata: ".$sdbc->lastError());
	$row     = $sdbc->nextData();
	
	if(crypt($_POST["password"], $row["user_password"]) != $row["user_password"])
		die("NOK;Password oder username incorrect!");
		
	$_SESSION["logged_in"] = 1;
	$_SESSION["uid"] = $row["user_uid"];
	
	$where = "profile_uid = ".$_SESSION["uid"];
	if($sdbc->select('profile', '*', $where))
		die("NOK;Could not access user profile: ".$sdbc->lastError());
	$row = $sdbc->nextData();
	
	$_SESSION["first_name"] = $row["profile_firstname"];
	$_SESSION["last_name"] = $row["profile_lastname"];
	
	echo "OK";
	$sdbc->close()
?>