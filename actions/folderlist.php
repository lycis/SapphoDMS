<?php
	if(!isset($_POST["area"]) ||
	   !isset($_POST["mode"])) die("Unknown mode.");
	   
	if($_POST["mode"] == "normal")
		session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if($_POST["area"] == "-- no area --") exit;
	
	$request = "SELECT area_aid FROM area WHERE area_name = '".mysql_real_escape_string($_POST["area"])."'";
	$result  = mysql_query($request) or die("Error while retrieving area data!");
	$row     = mysql_fetch_assoc($result);
	$area_id = $row["area_aid"];
	

	if($_POST["mode"] == "normal")
	{
		echo "<table>";
		echo "  <tr><td><input type=\"button\" id=\"filetree-button-add-item\" value=\"new item\"/></td>";
		echo "      <td><input type=\"button\" id=\"filetree-button-delete-item\" value=\"delete item\"/></td></tr>";
		echo "</table>";
	}
	
	echo "<div>";
	if($_POST["mode"] == "add-dialog" || $_POST["mode"] == "delete-dialog")
		$id = "fileview-dlg";
	else
		$id = "fileview";
	echo "<ul class=\"filetree\" id=\"$id\">";
	
	$folderclass = "";
	if($_POST["mode"] == "add-dialog")
		$folderclass = "add-object-parent";
	else if($_POST["mode"] == "delete-dialog")
		$folderclass = "";
	
	echo "<li><span class=\"folder\">";
	if($_POST["mode"] == "normal" || $_POST["mode"] == "delete-dialog")
		echo $_POST["area"];
	else if($_POST["mode"] == "add-dialog")
		echo "<a href=\"#\" class=\"$folderclass\" object_id=\"0\">".$_POST["area"]." (#0)</a>";
	echo "</span>";
	echo "<ul>";
	
	$request = "SELECT * FROM object WHERE object_areaid = $area_id AND object_parent = 0 AND object_deleted = 'N' ORDER BY object_name";
	$result  = mysql_query($request) or die("Error retrieving object data! ".mysql_error());
	while($row = mysql_fetch_assoc($result)){
		echo "<li>";
		
		$folderclass = "";
		$documentclass = "document_link";
		if($_POST["mode"] == "add-dialog")
			$documentclass = $folderclass = "add-object-parent";
		else if($_POST["mode"] == "delete-dialog")
			$documentclass = $folderclass = "delete-item";
		
		if($row["object_type"] == "F")
			dive_into_folder($row["object_id"], $area_id);
		else if($row["object_type"] = "D" && $_POST["mode"] != "add-dialog" && $_POST["mode"] != "delete-dialog")
			echo "<span class=\"file\"><a href=\"#\" class=\"$documentclass deletable\" document_id=\"".$row["object_id"]."\">".$row["object_name"]."</a></span>";
		else if($row["object_type"] = "D" && $_POST["mode"] = "delete-dialog")	
			echo 	"<span class=\"file\"><a href=\"#\" class=\"$documentclass\" object_id=\"".$row["object_id"].
					"\" object_name=\"".$row["object_name"]."\">".$row["object_name"]."</a></span>";
		echo "</li>";
	}
	
	mysql_close($db_conn);
	
	function dive_into_folder($folder_id, $area_id){
		$freq = "SELECT * FROM object WHERE object_id = $folder_id";
		$fres = mysql_query($freq) or die("Could not retrieve data of folder-object $folder_id");
		$frow = mysql_fetch_assoc($fres);
		$folder_name = $frow["object_name"];
		
		$folderclass = "";
		$documentclass = "document_link";
		if($_POST["mode"] == "add-dialog")
		{
			$folderclass = "add-object-parent";
			$documentclass = "";
		}
		else if($_POST["mode"] == "delete-dialog")
		{
			$folderclass = "delete-item";
			$documentclass = "delete-item";
		}
		
		echo "<li><span class=\"folder\">";
		if($_POST["mode"] == "normal")
			echo $folder_name;
		else if($_POST["mode"] == "add-dialog")
			echo "<a href=\"#\" class=\"$folderclass\" object_id=\"".$frow["object_id"]."\">$folder_name (#$folder_id)</a>";
		else if($_POST["mode"] == "delete-dialog")
			echo "<a href=\"#\" class=\"$folderclass\" object_id=\"".$frow["object_id"]."\">$folder_name</a>";
		echo "</span>";
		echo		"<ul>";
		
		$freq = "SELECT * FROM object WHERE object_areaid = ".$area_id." AND object_parent = $folder_id AND object_deleted = 'N' ORDER BY object_name";
		$fres = mysql_query($freq) or die("Could not retrieve items in folder $folder_id");
		while($frow = mysql_fetch_assoc($fres)){		
			echo "<li>";
			if($frow["object_type"] == "F")
				dive_into_folder($frow["object_id"], $area_id);
			else if($frow["object_type"] = "D" && $_POST["mode"] != "add-dialog" && $_POST["mode"] != "delete-dialog")
				echo "<span class=\"file\"><a href=\"#\" class=\"$documentclass deletable\" document_id=\"".$frow["object_id"]."\">".$frow["object_name"]."</a></span>";
			else if($frow["object_type"] = "D" && $_POST["mode"] = "delete-dialog")	
				echo 	"<span class=\"file\"><a href=\"#\" class=\"$documentclass\" object_id=\"".$frow["object_id"].
						"\" object_name=\"".$frow["object_name"]."\">".$frow["object_name"]."</a></span>";
			echo "</li>";
		}

		echo		"</ul>";
		echo "</li>";
	}
?>