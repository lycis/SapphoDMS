<?php
	session_start();
	include("../config.php");
	include("version_func.php");

	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("NOK;The database host is not available!");
	mysql_select_db($db_name) or die("NOK;The database is not accessible!");
	
	if(!isset($_POST["id"]) || !isset($_POST["version"])) echo "NOK;Not all data provided.";
	
	mysql_query("BEGIN") or die("NOK;Could not initiate transaction.");
	
	$_POST["id"] = mysql_real_escape_string($_POST["id"]);
	$_POST["version"] = mysql_real_escape_string($_POST["version"]);
	
	$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"];
	$result  = mysql_query($request) or db_die("NOK;Could not access object data.");
	$object  = mysql_fetch_assoc($result);
	
	if($object["object_locked_uid"] != 0 &&
	   $object["object_locked_uid"] != $_SESSION["uid"]) db_die("NOK;An other used has locked this object.");
	   
	$request = "SELECT * FROM object_data WHERE object_data_id = ".$_POST["id"];
	$result  = mysql_query($request) or db_die("NOK;Could not access current object data.");
	$object_data = mysql_fetch_assoc($result);
	if(mysql_num_rows($result) < 1) db_die("NOK;No object data available!");
	
	$request = "SELECT * FROM versioned_Data WHERE versioned_data_id = ".$_POST["id"].
	           " AND versioned_data_lnr = ".$_POST["version"];
	$result  = mysql_query($request) or db_die("NOK;Could not access the requested version.");
	$versioned_data = mysql_fetch_assoc($result);
	
	insert_versioned_record($object);
	
	$request = "UPDATE object_data SET object_data_text = '".$versioned_data["versioned_data_text"].
	           "', object_data_blob = '".$versioned_data["versioned_data_blob"]."', object_data_last_change = ".
			   "FROM_UNIXTIME(".time()."), object_data_last_user = ".$_SESSION["uid"]." WHERE object_data_id = ".
			   $_POST["id"];
	$result  = mysql_query($request) or db_die("NOK;Could not roll back to requested version!;".mysql_error());
	
	echo "OK;";
	
	mysql_query("COMMIT") or die("NOK;Error while commiting transaction.");
	mysql_close($db_conn);
	
	function db_die($text)
	{
		mysql_query("ROLLBACK") or die("Error while rollback of DB transaction.");
		die($text);
	}
?>