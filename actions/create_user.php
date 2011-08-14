<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if(!isset($_POST["username"]) || !isset($_POST["password"])) die("NOK;Please fill out the whole form!");
	
	if(!preg_match("/^[A-Za-z0-9_\.\-]{3,30}$/", $_POST["username"])) die("NOK;The username contains invalid characters!");
	
	$request = "insert into user(user_name, user_password) values('".
			    mysql_real_escape_string($_POST["username"])."', '".
				crypt($_POST["password"])."')";
	$result = mysql_query($request) or die("NOK;There was an error in the server request: ".mysql_error());
	echo "OK;Everything is fine :)";
	mysql_close($db_conn);
?>
	