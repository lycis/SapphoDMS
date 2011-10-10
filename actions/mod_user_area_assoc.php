<?php
	session_start();
	
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK###The database host is not available!");
	
	if(!isset($_POST["user"])) die("NOK###Pleas provide a user...");
	
	$sdbc->catalog_table('user');
	$where = $sdbc->queryOptions()->where('user_name', SapphoQueryOptions::EQUALS, $_POST["user"]);
	if($sdbc->select('user', 'user_uid', $where))
		die("NOK###No data to the referenced user!");
	$row     = $sdbc->nextData();
	$uid     = $row["user_uid"];
	
	$msg = "<td><select size=\"5\" id=\"assoc_has\">";
	
	$request = "SELECT * FROM area WHERE area.area_aid IN (SELECT user_area_aid FROM user_area WHERE user_area_uid = ".$uid.")";
	if($sdbc->execute($request))
		die("NOK###Could not get list of associated areas.");
	while($row = $sdbc->nextData()){
		$msg .= "<option>".$row["area_name"]."</option>";
	}
	$msg .= "</select></td>";
	$msg .= "<td><input type=\"button\" value=\"<<\" id=\"assoc_add_area\"/><br/><input type=\"button\" value=\">>\" id=\"assoc_remove_area\"/></td>";
	$msg .= "<td><select size=\"5\" id=\"assoc_has_not\">";
	
	$request = "SELECT * FROM area WHERE area.area_aid NOT IN (SELECT user_area_aid FROM user_area WHERE user_area_uid = ".$uid.")";
	if($sdbc->execute($request))
		die("NOK###Could not get list of associated areas.");
	while($row = $sdbc->nextData()){
		$msg .= "<option>".$row["area_name"]."</option>";
	}
	$msg .= "</selection></td>";
	
	echo "OK###$msg";
	$sdbc->close();
?>