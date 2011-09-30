<?php
	require_once("../lib/sdbc/sappho_dbc.php");
	
	$json = array("state" => "OK",
	              "error_message" => "");
				  
	if(!isset($_POST["type"]) ||
	   !isset($_POST["user"]) ||
	   !isset($_POST["password"]) ||
	   !isset($_POST["host"]) ||
	   !isset($_POST["name"]))
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
	
	$sqlfile = "../sql/sappho_structure_";
	if($sdbc->typeIs(SapphoDatabaseConnection::db_type_mysql))
		$sqlfile .= "mysql";
	else if($sdbc->typeIs(SapphoDatabaseConnection::db_type_postgre))
		$sqlfile .= "postgre";
	else
	{
		$json["state"] = "NK";
		$json["error_message"] = "Unknown database type '".$_POST["type"]."'";
		print json_encode($json);
		exit;
	}
	$sqlfile .= ".sql";
	
	$lines = file($sqlfile);
	if(count($lines) < 1)
	{
		$json["state"] = "NK";
		$json["error_message"] = "Error while accessing script! Please check $sqlfile exists and is readable!";
		print json_encode($json);
		exit;
	}
	
	$stmnt = "";
	$stmnt_cnt = 0;
	foreach($lines as $lnr => $line)
	{
		$line = trim($line);
		if($line == '') continue;
		if(substr($line, 0, 2) == "--") continue; 
		
		$stmnt .= $line;
		
		if(substr($line, -1) == ";")
		{
			$stmnt_cnt++;
			if($sdbc->execute($stmnt))
			{
				$json["state"] = "NK";
				$json["error_message"] = "Fatal error during table generation ($sqlfile, stmnt#$stmnt_cnt) [$stmnt] - ".
			                             $sdbc->lastError();
				$sdbc->rollbackTransaction();
				print json_encode($json);
				exit;
			}
			$stmnt = "";
		}
		else
			$stmnt .= " ";
	}
	
	$sdbc->close();
	
	print json_encode($json);
?>