<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK###The database host is not available!");
	mysql_select_db($db_name) or die("NOK###The database is not accessible!");
	
	if(!isset($_POST["user"])) die("NOK###Pleas provide a user...");
	
	
	$request = "SELECT user_uid FROM user WHERE user_name = '".$_POST["user"]."'";
	$result  = mysql_query($request) or die("NOK###No data to the referenced user!");
	$row     = mysql_fetch_assoc($result);
	$uid     = $row["user_uid"];
	
	$msg = "<td><select size=\"5\" id=\"assoc_has\">";
	
	$request = "SELECT * FROM area WHERE area.area_aid IN (SELECT user_area_aid FROM user_area WHERE user_area_uid = ".$uid.")";
	$result  = mysql_query($request) or die("NOK###Could not get list of associated areas.");
	while($row = mysql_fetch_assoc($result)){
		$msg .= "<option>".$row["area_name"]."</option>";
	}
	$msg .= "</select></td>";
	$msg .= "<td><input type=\"button\" value=\"<<\" id=\"assoc_add_area\"/><br/><input type=\"button\" value=\">>\" id=\"assoc_remove_area\"/></td>";
	$msg .= "<td><select size=\"5\" id=\"assoc_has_not\">";
	
	$request = "SELECT * FROM area WHERE area.area_aid NOT IN (SELECT user_area_aid FROM user_area WHERE user_area_uid = ".$uid.")";
	$result  = mysql_query($request) or die("NOK###Could not get list of associated areas.");
	while($row = mysql_fetch_assoc($result)){
		$msg .= "<option>".$row["area_name"]."</option>";
	}
	$msg .= "</selection></td>";
	
	echo "OK###$msg";
	mysql_close($db_conn);
?>