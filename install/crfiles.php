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
	

	print json_encode($json);
	
?>