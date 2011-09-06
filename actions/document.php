<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if(!isset($_POST["id"])) die("Error: No document!");
	if(!isset($_POST["mode"])) die("Error: No mode!");
	
	if($_POST["mode"] != "read" &&
	   $_POST["mode"] != "write" &&
	   $_POST["mode"] != "unlock" &&
	   $_POST["mode"] != "history" &&
	   $_POST["mode"] != "show_version") die("Error: invalid mode");
	
	// Get data
	$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"];
	$result  = mysql_query($request) or die("Document does not exist. (ID = ".$_POST["id"].", MODE = ".$_POST["mode"].")");
	$row     = mysql_fetch_assoc($result);
	
	if($_SESSION["uid"] == $row["object_locked_uid"] && $_POST["mode"] == "read")
		$_POST["mode"] = "write";
	
	if($_POST["mode"] == "read")
	{
		print_default_header($row);
		echo "<hr/>";
		echo getDocumentContent($_POST["id"]);
		echo "<hr/>";
	}
	else if($_POST["mode"] == "write")
	{
		// Lock record
		check_record_lock($row);
		
		print_default_header($row);
		
		$request = "BEGIN";
		mysql_query($request) or die("Could not start transaction to lock records: ".mysql_error());
		$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"]." FOR UPDATE";
		$result  = mysql_query($request) or rollback_die("Could not accquire lock on document records: ".mysql_error());
		
		$request = "UPDATE object SET object_locked_uid = ".$_SESSION["uid"]." WHERE object_id = ".$_POST["id"];
		$result  = mysql_query($request) or rollback_die("Could not set locking user in document record: ".mysql_error());
		mysql_query("COMMIT");
		
		include("ckeditor/ckeditor.php");
		// Include the CKEditor class.
		include_once "ckeditor/ckeditor.php";

		// Create a class instance.
		$CKEditor = new CKEditor();

		// Path to the CKEditor directory.
		$CKEditor->basePath = '/ckeditor/';

		// Set global configuration (used by every instance of CKEditor).
		if(isset($_POST["width"])) $CKEditor->config['width'] = $_POST["width"];
		if(isset($_POST["height"])) $CKEditor->config['height'] = $_POST["height"];

		// Change default textarea attributes.
		$CKEditor->textareaAttributes = array("cols" => 80, "rows" => 20);

		// The initial value to be displayed in the editor.
		$initialValue = getDocumentContent($_POST["id"]);

		// Create instance.
		$id = "document_".$_POST["id"];
		$CKEditor->editor($id, $initialValue);
		
		// Confirm dialog
		echo "<div id=\"confirm-dialog\" title=\"Cancel writing mode\">";
		echo "   <p>Do you really want to cancel? If you do all unsaved changes to the document will be lost.</p>";
		echo "</div>";
	}
	else if($_POST["mode"] == "unlock")
	{
		// Unlock record
		check_record_lock($row);
		
		$request = "BEGIN";
		mysql_query($request) or die("NOK;Could not start transaction to unlock records: ".mysql_error());
		$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"]." FOR UPDATE";
		$result  = mysql_query($request) or rollback_die("NOK;Could not accquire lock on document records: ".mysql_error());
		
		$request = "UPDATE object SET object_locked_uid = 0 WHERE object_id = ".$_POST["id"];
		$result  = mysql_query($request) or rollback_die("NOK;Could not remove locking user in document record: ".mysql_error());
		mysql_query("COMMIT");
		
		// return document id to AJAX
		echo $_POST["id"].";Record unlocked.";
	}
	else if($_POST["mode"] == "history")
	{
		$request = "SELECT object_data_last_change, object_data_last_user FROM object_data WHERE object_data_id = ".$row["object_id"];
		$result  = mysql_query($request) or die("Could not get the documents latest change data.");
		$change  = mysql_fetch_assoc($result);
		
		$username = "???";
		$request = "SELECT user_name FROM user WHERE user_uid = ".$change["object_data_last_user"];
		$result  = mysql_query($request) or $username = "<unknown>";
		if($username != "<unknown>"){
			$user    = mysql_fetch_assoc($result);
			$username = $user["user_name"];
		}
		
		echo "<h3>History of ".$row["object_name"]."</h3>";
		echo "<table><tr><td><input type=\"button\" value=\"show document\" id=\"document_show\"/></td></tr></table>";
		echo "<table align=\"center\">";
		echo "<tr><th>Version #</th><th>Versioned Time</th><th>User</th><th></th><th></th></tr>";
		echo "<tr><td>current</td><td>".$change["object_data_last_change"]."</td><td>$username</td>";
		echo "<td></td></tr>";
		
		$request = "SELECT versioned_data_lnr, versioned_data_time, versioned_data_user ".
                   "FROM versioned_data WHERE versioned_data_id = ".$row["object_id"].
		           " ORDER BY versioned_data_lnr DESC";
		$result  = mysql_query($request) or die("Could not get version data: ".mysql_error());
		while($version = mysql_fetch_assoc($result)){
		    $vnr = $version["versioned_data_lnr"];
			$vtime = $version["versioned_data_time"];
			
			$vuser   = "???";
			$urequest = "SELECT * FROM user WHERE user_uid = ".$version["versioned_data_user"];
			$uresult  = mysql_query($urequest) or $vuser = "<unknown>";
			if($vuser != "<unknown>"){
				$user    = mysql_fetch_assoc($uresult);
				$vuser   = $user["user_name"];
			}
			
			echo "<tr><td>$vnr</td><td>$vtime</td><td>$vuser</td><td><input type=\"button\" value=\"show\" version=\"$vnr\" class=\"document_show_version\"/></td>";
			echo "<td><input type=\"button\" value=\"restore\" version=\"$vnr\" class=\"document_restore_version\"/></td></tr>";
		}
		
		print_hidden_fields();
	}
	else if($_POST["mode"] == "show_version")
	{
		if(!isset($_POST["version"])) die("No version to restore.");
		
		echo "<h3>".$row["object_name"]." (Version# ".$_POST["version"].")</h3>";
		echo "<p>This is an archived version. You can not edit it.</p>";
		echo "<hr/>";
		echo getDocumentContentFromVersion($_POST["id"], $_POST["version"]);
		echo "<hr/>";
	}
	
	function getDocumentContent($did)
	{
		$request = "SELECT object_type FROM object WHERE object_id = $did";
		$result  = mysql_query($request) or die("Error while loading document: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested document (#$did) does not exist.");
		
		$row = mysql_fetch_assoc($result);
		$oid = $row["object_type"];
		if($oid != "D") die("This document can not be displayed!");
		
		$request = "SELECT object_data_text FROM object_data WHERE object_data_id = $did";
		$result  = mysql_query($request) or die("Error while loading document data: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested document (#$did) does not have any data.");
		$row = mysql_fetch_assoc($result);
		
		return $row["object_data_text"];
	}
	
	function getDocumentContentFromVersion($did, $version)
	{
		$request = "SELECT object_type FROM object WHERE object_id = $did";
		$result  = mysql_query($request) or die("Error while loading versioned document: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested version (Document# $did, Version# $version) does not exist.");
		
		$row = mysql_fetch_assoc($result);
		$oid = $row["object_type"];
		if($oid != "D") die("This document can't be displayed!");
		
		$request = "SELECT versioned_data_text FROM versioned_data WHERE versioned_data_id = $did AND versioned_data_lnr = $version";
		$result  = mysql_query($request) or die("Error while loading versioned document data: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested document (Document# $did, Version# $version) does not have any data.");
		$row = mysql_fetch_assoc($result);
		
		return $row["versioned_data_text"];
	}
	
	function rollback_die($text){
			mysql_query("ROLLBACK");
			die($text);
	}
	
	function check_record_lock($row){
		if($row["object_locked_uid"] != 0 &&
		   $row["object_locked_uid"] != $_SESSION["uid"]){
			$locked_uid = $row["object_locked_uid"];
			$request = "SELECT user_name FROM user where user_uid = $locked_uid";
			$result  = mysql_query($request) or die("Record is locked by unknown user (Error: ".mysql_error().")");
			
			if(mysql_num_rows($result) < 1) die("Record is locked by deleted user (uid = $locked_uid)");
			$user_row = mysql_fetch_assoc();
			
			die("Document is locked by another user. (UID = ".$locked_uid.", USER_NAME = ".$user_row["user_name"].")");
		}
		return true;
	}
	
	function print_default_header($row){
		$request = "SELECT object_data_last_change, object_data_last_user FROM object_data WHERE object_data_id = ".$row["object_id"];
		$result  = mysql_query($request) or die("Error while retrieving last change data: ".mysql_error());
		$change  = mysql_fetch_assoc($result);
		
		$username = "???";
		$request = "SELECT user_name FROM user WHERE user_uid = ".$change["object_data_last_user"];
		$result  = mysql_query($request) or $username = "<unknown>";
		if($username != "<unknown>"){
			$user    = mysql_fetch_assoc($result);
			$username = $user["user_name"];
		}
		
		
		// Caption
		echo "<h3>".$row["object_name"]."</h3>";
		echo "<p>Last change at ".$change["object_data_last_change"]." by $username</p>";
	
		// Buttons
		echo "<table><tr><td><input type=\"button\" value=\"edit\" id=\"document_edit\"/></td>";
		echo "<td><input type=\"button\" value=\"delete\" id=\"document_delete\"/></td>";
		echo "<td><input type=\"button\" value=\"save\" id=\"document_save\"/></td>";
		echo "<td><input type=\"button\" value=\"exit\" id=\"document_cancel\"/></td>";
		echo "<td><input type=\"button\" value=\"show history\" id=\"document_history\"/></td></tr></table>";
		
		print_hidden_fields();
	}
	
	function print_hidden_fields(){
		echo "<input type=\"hidden\" id=\"document_mode\" value=\"".$_POST["mode"]."\"/>";
		echo "<input type=\"hidden\" id=\"document_id\" value=\"".$_POST["id"]."\"/>";
		echo "<input type=\"hidden\" id=\"document_cancel\" value=\"0\"/>";
	}
?>