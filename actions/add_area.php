<?php
	session_start();
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	if(!isset($_POST["area"])) die("NOK;Please provide an area name!");
	
	$where = "area_name = '".$_POST["area"]."'";
    if($sdbc->select('area', '*', $where))
		die("NOK;Error while checking existence!");
	$row_num = $sdbc->nextData();
	
	if($row_num > 0)
		die("NOK;Area already exists!");
	
	$request = "INSERT INTO area(area_name) values('".$_POST["area"]."')";
	if($sdbc->insert('area', array('area_name' => $_POST["area"]))) 
		die("NOK;Error while creation of new are: ".mysql_error());
	
	echo "OK;";
	$sdbc->close();
?>