<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if($_POST["area"] == "-- no area --") exit;
	
	$request = "SELECT area_aid FROM area WHERE area_name = '".mysql_real_escape_string($_POST["area"])."'";
	$result  = mysql_query($request) or die("Error while retrieving area data!");
	$row     = mysql_fetch_assoc($result);
	$area_id = $row["area_aid"];
	
	echo "<div>";
	echo "<ul class=\"filetree\" id=\"fileview\">";
	
	$request = "SELECT * FROM object WHERE object_areaid = $area_id AND object_parent = 0 ORDER BY object_name";
	$result  = mysql_query($request) or die("Error retrieving object data! ".mysql_error());
	while($row = mysql_fetch_assoc($result)){
		echo "<li>";
		if($row["object_type"] == "F")
			dive_into_folder($row["object_areaid"], $area_id);
		else if($row["object_type"] = "D")
			echo "<span class=\"file\"><a href=\"#\" class=\"document_link\" document_id=\"".$row["object_id"]."\">".$row["object_name"]."</a></span>";
		echo "</li>";
	}
	
	mysql_close($db_conn);
	
	function dive_into_folder($folder_id, $area_id){
		$freq = "SELECT * FROM object WHERE object_id = $folder_id";
		$fres = mysql_query($freq) or die("Could not retrieve data of folder-object $folder_id");
		$frow = mysql_fetch_assoc($fres);
		$folder_name = $frow["object_name"];
		
		echo "<li><span class=\"folder\">".$folder_name."</span>";
		echo		"<ul>";
		
		$freq = "SELECT * FROM object WHERE object_areaid = ".$area_id." AND object_parent = $folder_id ORDER BY object_name";
		$fres = mysql_query($freq) or die("Could not retrieve items in folder $folder_id");
		while($frow = mysql_fetch_assoc($fres)){
			echo "<li>";
			if($frow["object_type"] == "F")
				dive_into_folder($frow["object_id"], $area_id);
			else if($frow["object_type"] = "D")
				echo "<span class=\"file\"><a href=\"#\" class=\"document_link\" document_id=\"".$frow["object_id"]."\">".$frow["object_name"]."</a></span>";
			echo "</li>";
		}
		
		echo		"</ul>";
		echo "</li>";
	}
?>