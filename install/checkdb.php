<?php
	require_once("../lib/sdbc/sappho_dbc.php");
	
	$json = array("state" => "OK",
	              "error_message" => "");
	
	
	if(!isset($_POST["type"])) $_POST["type"] = "mysql";
	if(!isset($_POST["host"])) $_POST["host"] = "localhost";
	if(!isset($_POST["name"])) $_POST["name"] = "";
	if(!isset($_POST["user"])) $_POST["user"] = "";
	if(!isset($_POST["password"])) $_POST["password"] = "";
	
	$type = "";
	if($_POST["type"] == "mysql")
		$type = SapphoDatabaseConnection::db_type_mysql;
	else if($_POST["type"] == "postgre")
		$type = SapphoDatabaseConnection::db_type_postgre;
	
	$sdbc = new SapphoDatabaseConnection($type,
	                                     $_POST["host"],
								 	 	 $_POST["name"],
										 $_POST["user"]);
	if(!$sdbc)
	{
		$json["state"] = "NK";
		$json["error_message"] = "Could not use SDBC connector!";
	}
	
	$conn = 0; 
	if($json["state"] != "NK" && ($conn = @$sdbc->connect($_POST["password"])))
	{
		$json["state"] = "NK";
		$reason = "unknown";
		
		if($conn == SapphoDatabaseConnection::db_connect_declined)
			$reason = "connection declined by server";
		if($conn == SapphoDatabaseConnection::db_connect_missing_data)
			$reason = "please provide all login options";
		if($conn == SapphoDatabaseConnection::db_connect_db_notexist)
			$reason = "database does not exist";
		$json["error_message"] = "Could not establish connection ($conn; ".$sdbc->lastError().")";
	}
	
	$sdbc->close();
	
	print json_encode($json);
?>