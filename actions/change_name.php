<?php	
	session_start();
	
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");

	if(!isset($_POST["first"]) || !isset($_POST["last"]))
		die("NOK;nodata;".$_POST["first"]." ".$_POST["last"]);
		
	$where = $sdbc->queryOptions()->where('profile_uid', SapphoQueryOptions::EQUALS, $_SESSION["uid"]);
	if($sdbc->update('profile',
	                 array('profile_firstname' => $_POST["first"],
	                        'profile_lastname' => $_POST["last"]),
					 $where))
		die("NOK;$request;".$sdbc->lastError());
	
	$_SESSION["first_name"] = $_POST["first"];
	$_SESSION["last_name"]  = $_POST["last"];
	
	echo "OK";
	$sdbc->close();
?>