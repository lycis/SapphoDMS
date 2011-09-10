<?php
	session_start();
	if(!isset($_POST["oid"])) die("NOK;Please provide all data.");
	if(!isset($_SESSION["logged_in"])) die("NOK;Please log in.");
	   
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	
	$request = "SELECT * FROM object WHERE object_id = ".$_POST["oid"];
	$result  = mysql_query($request) or die("NOK;Error while checking existance.");
	if(mysql_num_rows($result) != 1) die("NOK;Object does not exist.");
	$object = mysql_fetch_assoc($result);
	
	if($object["object_type"] == "F")
	{
		$request = "SELECT object_id FROM object WHERE object_deleted = 'N' AND object_parent = ".$_POST["oid"];
		$result  = mysql_query($request) or die("NOK;Could not check if folder is empty.");
		if(mysql_num_rows($result) > 0) die("NOK;The folder is not empty. ".mysql_num_rows($result));
	}
	
	$request = "UPDATE object SET object_deleted = 'Y' WHERE object_id = ".$_POST["oid"];
	$result  = mysql_query($request) or die("NOK;Could not delete object.");
	
	
	
	echo "OK;Object deleted.";
	mysql_close($db_conn);
?>