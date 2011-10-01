<?php
	// is executed whenever index.php is processed
	
	include("config.php");
	include("lib/sdbc/sappho_dbc.php");
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("The database is not accessible!");
?>