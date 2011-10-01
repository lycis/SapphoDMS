<?php	
	session_start();
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	$sdbc = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($sdbc->connect($db_password)) die("NOK;The database host is not available!");
	
	if(!isset($_POST["user"]) || !isset($_POST["area"])) die("NOK;Please provide complete data.");
	
	$where = "area_name = '".mysql_real_escape_string($_POST["area"])."'";
	if($sdbc->select('area', 'area_aid', $where))
		die("NOK;Could not retrieve AreaId - ".$sdbc->lastError());
	$row     = $sdbc->nextData();
	$area_id = $row["area_aid"];
	
	$where = "user_name = '".$_POST["user"]."'";
	if($sdbc->select('user', 'user_uid', $where))
		die("NOK;Could not retrieve UserId.");
	$row     = $sdbc->nextData();
	$user_id = $row["user_uid"];
	
	$where = "user_area_aid = $area_id AND user_area_uid = $user_id";
	if($sdbc->select('user_area', '*', $where))
		die("NOK;Could not get association status - ".$sdbc->lastError());
	$row_count = $sdbc->rowCount();
	
	if($row_count > 0)
		die("NOK;The user is already associated to the area!");
	
	$ins = $sdbc->insert('user_area', array('user_area_uid' => $user_id,
	                                     'user_area_aid' => $area_id));
	if($ins) 
		die("NOK;Could not add new area to user - ".$sdbc->lastError());
	
	echo "OK;";
	$sdbc->close();
?>