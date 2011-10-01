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
		$json["error_message"] = "Missing data!";
		print json_encode($json);
		exit;
	}
	
	$type = "";
	if($_POST["type"] == "mysql")
		$type = SapphoDatabaseConnection::db_type_mysql;
	else if($_POST["type"] == "postgre")
		$type = SapphoDatabaseConnection::db_type_postgre;
	
	$filedata  = "<?php\n";
	$filedata .= "/*\n";
	$filedata .= " * Configuration updated at \n".date('l jS \of F Y h:i:s A');
	$filedata .= " */\n";
	$filedata .= '$db_user      = "'.$_POST["user"]."\";\n";
	$filedata .= '$db_password  = "'.$_POST["password"]."\";\n";
	$filedata .= '$db_name      = "'.$_POST["name"]."\";\n";
	$filedata .= '$db_host      = "'.$_POST["host"]."\";\n";
	$filedata .= '$db_type      = "'.$type."\";\n";
	$filedata .= "?>";
	
	$file = false;
	if(!($file = fopen('../config.php', 'w')))
	{
		$json["state"] = "NK";
		$json["error_message"] = "could not access config.php.";
		$json["content"] = $filedata;
		print json_encode($json);
		exit;
	}
	
	fwrite($file, $filedata);
	fclose($file);
	
	if(!($file = fopen('installed', 'w')))
	{
		$json["state"] = "NK";
		$json["error_message"] = "could not set installed flag!";
		$json["content"] = "Please create a file named 'installed' in the install directory to ".
		                   "prevent anybody from accessing the installation procedure!";
		print json_encode($json);
		exit;
	}
	
	fwrite($file, "system is configured");
	fclose($file);
	
	print json_encode($json);
	
?>