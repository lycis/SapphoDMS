<?php	
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");

	if(!isset($_POST["first"]) || !isset($_POST["last"]))
		die("NOK;nodata;".$_POST["first"]." ".$_POST["last"]);
		
	$_POST["last"] = mysql_real_escape_string($_POST["last"]);
	$_POST["first"] = mysql_real_escape_string($_POST["first"]);
		
	$request = "UPDATE profile ".
	           "SET profile_firstname = '".$_POST["first"]."', profile_lastname = '".$_POST["last"]."' ".
			   "WHERE profile_uid = ".$_SESSION["uid"];
	$result  = mysql_query($request) or die("NOK;$request;".mysql_error());
	
	$_SESSION["first_name"] = $_POST["first"];
	$_SESSION["last_name"]  = $_POST["last"];
	
	echo "OK";
	mysql_close($db_conn);
?>