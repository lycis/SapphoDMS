<?php
	session_start();
	if(!isset($_POST["parent"]) || !isset($_POST["name"]) ||
       !isset($_POST["type"]) || !isset($_POST["area"])) die("NOK;Please provide all data.");
	   
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	   
	$_POST["name"] = mysql_real_escape_string($_POST["name"]);
	$_POST["type"] = mysql_real_escape_string($_POST["type"]);
	$_POST["area"] = mysql_real_escape_string($_POST["area"]);
	
	if($_POST["type"] != "D" && $_POST["type"] != "F") die("NOK;Invalid document type");
	
	$request = "SELECT area_aid FROM area WHERE area_name = '".$_POST["area"]."'";
	$result  = mysql_query($request) or die("NOK;Could not accquire area data: ".mysql_error());
	$row     = mysql_fetch_assoc($result);
	$area_id = $row["area_aid"];
	   
	$request = "SELECT * FROM object WHERE object_name = '".$_POST["name"]."' AND object_parent = ".$_POST["parent"].
	           " AND object_areaid = $area_id AND object_type = '".$_POST["type"]."'";
	$result  = mysql_query($request) or die("NOK;Error while checking existence: ".mysql_error());
	if(mysql_num_rows($result) > 0) die("NOK;A document with this name already exists.");
	
	$request = "INSERT INTO object(object_name, object_parent, object_type, object_areaid) ".
	           " VALUES('".$_POST["name"]."'".",".$_POST["parent"].", '".$_POST["type"]."', $area_id)";
	$result  = mysql_query($request) or die("NOK;Error while creation of object: ".mysql_error());
	$oid     = mysql_insert_id();
	
	if($_POST["type"] == "D")
	{
		$request = "INSERT INTO object_data(object_data_id, object_data_text, object_data_last_change, object_data_last_user) ".
		           "VALUES($oid, '', FROM_UNIXTIME(".time()."), ".$_SESSION["uid"].")";
		$result  = mysql_query($request) or 
			die("NOK;Document was created, but the System was not able to create an object data record!");
	}
	
	echo "OK;Object created.";
?>