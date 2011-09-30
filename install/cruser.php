<?php
	require_once("../lib/sdbc/sappho_dbc.php");
	
	$json = array("state" => "OK",
	              "error_message" => "");
				  
	if(!isset($_POST["type"]) ||
	   !isset($_POST["user"]) ||
	   !isset($_POST["password"]) ||
	   !isset($_POST["host"]) ||
	   !isset($_POST["name"]) ||
	   !isset($_POST["auser"]) ||
	   !isset($_POST["apwd"]))
	{
		$json["state"] = "NK";
		$json["error_message"] = "Please provide all login data!";
		print json_encode($json);
		exit;
	}
	
	$sdbc = new SapphoDatabaseConnection($_POST["type"],
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
		$json["error_message"] = "Could not establish connection (".$sdbc->lastError().")";
		print json_encode($json);
		exit;
	}
	
	if(!$sdbc->startTransaction())
	{
		$json["state"] = "NK";
		$json["error_message"] = "Could not start transaction - ".$sdbc->lastError();
		print json_encode($json);
	}
	
	$ins = $sdbc->insert("user", array("user_name" => $_POST["auser"],
	                            "user_password" => crypt($_POST["apwd"])));
	if($ins)
	{
		$reason = "";
		if($ins == SapphoDatabaseConnection::db_error_wrong_dtype)
			$reason = "datatype error";
		$json["state"] = "NK";
		$json["error_message"] = "Fatal error during user creation - ".
			                     $sdbc->lastError();	
		print json_encode($json);
		$sdbc->rollbackTransaction();
	}
	$sdbc->commitTransaction();
	
	$sdbc->close();
	
	print json_encode($json);
?>