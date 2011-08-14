<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	
	if(!isset($_POST["area"])) die("NOK;Please provide an area name!");
	
	$request = "SELECT * FROM area WHERE area_name = '".mysql_real_escape_string($_POST["area"])."'";
	$result  = mysql_query($request) or die("NOK;Error while checking existence!");
	$row_num = mysql_num_rows($result);
	
	if($row_num < 1)
		die("NOK;Area does not exist!");
	
	$request = "DELETE FROM area WHERE area_name = '".mysql_real_escape_string($_POST["area"])."'";
	$result  = mysql_query($request) or die("NOK;Error while deletion of area: ".mysql_error());
	
	echo "OK";
	mysql_close($db_conn);
?>