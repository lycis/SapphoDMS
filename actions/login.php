<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if(!isset($_POST["username"]) || !isset($_POST["password"])) die("NOK");
	
	if(!preg_match("/^[A-Za-z0-9_\.\-]{3,30}$/", $_POST["username"])) die("NOK");
	
	$request = "SELECT * FROM user WHERE user_name = '".mysql_real_escape_string($_POST["username"])."'";
	$result  = mysql_query($request) or die("NOK");
	$row     = mysql_fetch_assoc($result);
	
	if(crypt($_POST["password"], $row["user_password"]) != $row["user_password"])
		die("NOK");
		
	$_SESSION["logged_in"] = 1;
	$_SESSION["uid"]       = $row["user_uid"];
	
	$request = "SELECT * FROM profile WHERE profile_uid = ".$_SESSION["uid"];
	$result  = mysql_query($request) or die("NOK");
	$row     = mysql_fetch_assoc($result);
	
	$_SESSION["first_name"] = $row["profile_firstname"];
	$_SESSION["last_name"] = $row["profile_lastname"];
	
	echo "OK";
	mysql_close($db_conn);
?>