<?php	
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	
	if(!isset($_POST["user"]) || !isset($_POST["area"])) die("NOK;Please provide complete data.");
	
	$request = "SELECT area_aid FROM area WHERE area_name = '".mysql_real_escape_string($_POST["area"])."'";
	$result  = mysql_query($request) or die("NOK;Could not retrieve AreaId.");
	$row     = mysql_fetch_assoc($result);
	$area_id = $row["area_aid"];
	
	$request = "SELECT user_uid FROM user WHERE user_name = '".mysql_real_escape_string($_POST["user"])."'";
	$result  = mysql_query($request) or die("NOK;Could not retrieve UserId.");
	$row     = mysql_fetch_assoc($result);
	$user_id = $row["user_uid"];
	
	$request = "SELECT * FROM user_area WHERE user_area_aid = $area_id AND user_area_uid = $user_id";
	$result  = mysql_query($request) or die("NOK;Could not get association status!");
	$row_count = mysql_num_rows($result);
	
	if($row_count < 1)
		die("NOK;The user is not associated to the area!");
	
	$request = "DELETE FROM user_area WHERE user_area_aid = $area_id AND user_area_uid = $user_id";
	$result  = mysql_query($request) or die("NOK;Could not remove are from user - ".mysql_error());
	
	echo "OK;";
	mysql_close($db_conn);
?>