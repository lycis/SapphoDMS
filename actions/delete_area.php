<?php
	session_start();
	
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	if(!isset($_POST["area"])) die("NOK;Please provide an area name!");
	
	
	$where = $sdbc->queryOptions()->where('area_name', SapphoQueryOptions::EQUALS, $_POST["area"]);
	if($sdbc->select('area', '*', $where))
		die("NOK;Error while checking existence!");
	$row_num = $sdbc->rowCount();
	
	if($row_num < 1)
		die("NOK;Area does not exist!");
	
	$where = $sdbc->queryOptions()->where('area_name', SapphoQueryOptions::EQUALS, $_POST["area"]);
	if($sdbc->delete('area', $where))
		die("NOK;Error while deletion of area: ".mysql_error());
	
	echo "OK";
	$sdbc->close();
?>