<?php
	session_start();
	include("../config.php");
	include("version_func.php");
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
?>