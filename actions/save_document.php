<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	
	if(!isset($_POST["id"]) || !isset($_POST["content"])) die("NOK;Pleas provide all data.");
	
	$_POST["content"] = mysql_real_escape_string($_POST["content"]);
	
	$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"];
	$result  = mysql_query($request) or die("NOK;Error while checking object existence - ".mysql_error());
	if(mysql_num_rows($result) < 1) die("NOK;Document does not exist.");
	$object  = mysql_fetch_assoc($result);
	
	if($object["object_locked_uid"] != $_SESSION["uid"]) die("NOK;You did not lock the document!");
	
	if($object["object_type"] == "D")
	{
		insert_versioned_record($object);
		$request = "UPDATE object_data SET object_data_text = '".$_POST["content"]."', object_data_last_change = ".
                   "FROM_UNIXTIME(".time()."), object_data_last_user = ".$_SESSION["uid"]." WHERE object_data_id = ".$_POST["id"];
	}
	else
		die("NOK;You can not change this type of document!");
	
	$result = mysql_query($request) or die("NOK;Could not update object content - ".mysql_error());
	echo "OK;Document updated.";
	
	function insert_versioned_record($object)
	{
		// check if versioned data exists
		$request = "SELECT versioned_data_lnr FROM versioned_data WHERE versioned_data_id = ".$object["object_id"];
		$result  = mysql_query($request) or die("NOK;Could not verify versioned data existence - ".mysql_error());
		if(mysql_num_rows($result) < 1) $vlnr = 1;
		else
		{
			$request = "SELECT MAX(versioned_data_lnr) AS 'lnr' ".
					   "FROM versioned_data ".
					   "WHERE versioned_data_id = ".$object["object_id"];
			$result  = mysql_query($request) or die("NOK;Could not calculate version number.");
			$row     = mysql_fetch_assoc($result);
			$vlnr    = $row["lnr"]+1;
		}
		
		$request = "SELECT * FROM object_data WHERE object_data_id = ".$object["object_id"];
		$result  = mysql_query($request) or die("NOK;Error while versioning - ".mysql_error());
		$object_data = mysql_fetch_assoc($result);
		
		if($object["object_type"] = "D")
			$request = "INSERT INTO versioned_data(versioned_data_lnr, versioned_data_text, versioned_data_id, versioned_data_time, versioned_data_user) ".
			           "VALUES($vlnr, '".$object_data["object_data_text"]."', ".$object["object_id"].", FROM_UNIXTIME(".time()."), ".
					   $object_data["object_data_last_user"].")";
		else
			die("NOK;You can not change this type of document!");
		
		$result = mysql_query($request) or die("NOK;Could not create versioned record - ".mysql_error());
		return true;
	}
?>