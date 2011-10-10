<?php
	session_start();
	if(!isset($_POST["oid"])) die("NOK;Please provide all data.");
	if(!isset($_SESSION["logged_in"])) die("NOK;Please log in.");
	   
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	$where = $sdbc->queryOptions()->where('object_id', SapphoQueryOptions::EQUALS, $_POST["oid"]);
	if($sdbc->select('object', '*', $where))
		die("NOK;Error while checking existance.");
	if($sdbc->rowCount() != 1) die("NOK;Object does not exist.");
	$object = $sdbc->nextData();
	
	if($object["object_type"] == "F")
	{
		$where = $sdbc->queryOptions()->where('object_deleted', SapphoQueryOptions::EQUALS, 'N')
		                              ->andWhere('object_parent', SapphoQueryOptions::EQUALS, $_POST["oid"]);
		if($sdbc->select('object', 'object_id', $where))
			die("NOK;Could not check if folder is empty.");
		if($sdbc->rowCount() > 0) die("NOK;The folder is not empty. ".$sdbc->lastError());
	}
	
	$where = $sdbc->queryOptions()->where('object_id', SapphoQueryOptions::EQUALS, $_POST["oid"]);
	if($sdbc->update('object', array('object_deleted' => 'Y'), $where))
		die("NOK;Could not delete object.");
	
	
	
	echo "OK;Object deleted.";
	$sdbc->close();
?>