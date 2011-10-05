<?php
	session_start();
	if(!isset($_POST["parent"]) || !isset($_POST["name"]) ||
       !isset($_POST["type"]) || !isset($_POST["area"])) die("NOK;Please provide all data.");
	   
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	   
	$_POST["name"] = mysql_real_escape_string($_POST["name"]);
	$_POST["type"] = mysql_real_escape_string($_POST["type"]);
	$_POST["area"] = mysql_real_escape_string($_POST["area"]);
	
	if($_POST["type"] != "D" && $_POST["type"] != "F") die("NOK;Invalid document type");
	
	$where = "area_name = '".$_POST["area"]."'";
	if($sdbc->select('area', 'area_aid', $where))
		die("NOK;Could not accquire area data: ".mysql_error());
	$row     = $sdbc->nextData();
	$area_id = $row["area_aid"];
	   
	$where = "object_name = '".$_POST["name"]."' AND object_parent = ".$_POST["parent"].
	         " AND object_areaid = $area_id AND object_type = '".$_POST["type"]."'";
	if($sdbc->select('object', '*', $where))
		die("NOK;Error while checking existence: ".mysql_error());
	if($sdbc->rowCount() > 0) die("NOK;A document with this name already exists.");
	
	if($sdbc->insert('object', array('object_name' => $_POST["name"],
	                                  'object_parent' => $_POST["parent"],
									  'object_type' => $_POST["type"],
									  'object_areaid' => $area_id)))
		die("NOK;Error while creation of object: ".mysql_error());
	$oid = $sdbc->lastId();
	
	if($_POST["type"] == "D")
	{
		$request = "INSERT INTO object_data(object_data_id, object_data_text, object_data_last_change, object_data_last_user) ".
		           "VALUES($oid, '', FROM_UNIXTIME(".time()."), ".$_SESSION["uid"].")";
		$data = array('object_data_id' => $oid,
		               'object_data_text' => '',
					   'object_data_last_change' => 'FROM_UNIXTIME('.time().')',
					   'object_data_last_user' => $_SESSION["uid"]);
		if($sdbc->insert('object_data', $data))
			die("NOK;Document was created, but the System was not able to create an object data record! [".$sdbc->lastError()."]");
	}
	
	echo "OK;Object created.";
?>