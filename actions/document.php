<?php
	session_start();
	include("../config.php");
	include("../lib/sdbc/sappho_dbc.php");
	
	global $db;
	$db = new SapphoDatabaseConnection($db_type, $db_host, $db_name, $db_user);
	if($db->connect($db_password)) die("database error: ".$db->lastError());
	
	if(!isset($_POST["id"])) die("Error: No document!");
	if(!isset($_POST["mode"])) die("Error: No mode!");
	
	if($_POST["mode"] != "read" &&
	   $_POST["mode"] != "write" &&
	   $_POST["mode"] != "unlock" &&
	   $_POST["mode"] != "history" &&
	   $_POST["mode"] != "show_version") die("Error: invalid mode");
	
	// Get data
	if($db->select('object', array('*'),
	               "object_id = ".$_POST["id"])) die("Document does not exist. (ID = ".$_POST["id"].", MODE = ".$_POST["mode"].")");;
	$row     = $db->nextData();
	
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
		
		if($db->execute("BEGIN")) die("Could not start transaction to lock records: ".$db->lastError());
		
		$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"]." FOR UPDATE";		
		if($db->execute($request)) rollback_die("Could not accquire lock on document records: ".$db->lastError());
		
		$data = array("object_locked_uid" => $_SESSION["uid"]);
		if($db->update('object', $data, "object_id = ".$_POST["id"])) 
			rollback_die("Could not set locking user in document record: ".$db->lastError());
		if($db->execute("COMMIT")) die("Could not commit transaction: ".$db->lastError());
		
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
		
		if($db->execute("BEGIN")) die("NOK;Could not start transaction to unlock records: ".mysql_error());
		$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"]." FOR UPDATE";
		if($db->execute($request)) rollback_die("NOK;Could not accquire lock on document records: ".mysql_error());
		
		$data = array("object_locked_uid" => 0);
		if($db->update('object', $data, 'object_id = '.$_POST["id"])) 
			rollback_die("NOK;Could not remove locking user in document record: ".$db->lastError());
		$db->execute("COMMIT");
		
		// return document id to AJAX
		echo $_POST["id"].";Record unlocked.";
	}
	else if($_POST["mode"] == "history")
	{
		include('history.php');
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
		global $db;
		
		if($db->select('object', array('object_type'), "object_id = $did")) 
			die("Error while loading document: ".mysql_error());
		$row = $db->nextData() or die("Requested document (#$did) does not exist.");
		
		$oid = $row["object_type"];
		if($oid != "D") die("This document can not be displayed!");
		
		if($db->select('object_data', array('object_data_text'), "object_data_id = $did"))
			die("Error while loading document data: ".mysql_error());
		$row = $db->nextData() or die("Requested document (#$did) does not have any data.");
		
		return $row["object_data_text"];
	}
	
	function getDocumentContentFromVersion($did, $version)
	{
		global $db;
		if($db->select('object', 'object_type', "object_id = $did"))
			die("Error while loading versioned document: ".mysql_error());
		$row = $db->nextData() or die("Requested version (Document# $did, Version# $version) does not exist.");
		
		$oid = $row["object_type"];
		if($oid != "D") die("This document can't be displayed!");
		
		if($db->select('versioned_data', 'versioned_data_text', 
		               "versioned_data_id = $did AND versioned_data_lnr = $version"))
			die("Error while loading versioned document data: ".$db->lastError());
		$row = $db->nextData() or die("Requested document (Document# $did, Version# $version) does not have any data.");
		
		return $row["versioned_data_text"];
	}
	
	function rollback_die($text){
		global $db;
		$db->execute("ROLLBACK");
			die($text);
	}
	
	function check_record_lock($row){
		global $db;
		
		if($row["object_locked_uid"] != 0 &&
		   $row["object_locked_uid"] != $_SESSION["uid"]){
			$locked_uid = $row["object_locked_uid"];
			
			if($db->select('user', 'user_name', "user_uid = $locked_uid"))
				die("Record is locked by unknown user (Error: ".$db->lastError().")");
			
			$user_row = $db->nextData() or die("Record is locked by deleted user (uid = $locked_uid)");
			
			die("Document is locked by another user. (UID = ".$locked_uid.", USER_NAME = ".$user_row["user_name"].")");
		}
		return true;
	}
	
	function print_default_header($row){
		global $db;
		
		if($db->select('object_data', array('object_data_last_change', 'object_data_last_user'), 
		               "object_data_id = ".$row["object_id"]))
			die("Error while retrieving last change data: ".mysql_error());
		$change  = $db->nextData();
		
		$username = "???";
		if($db->select('user', 'user_name', "user_uid = ".$change["object_data_last_user"]))
			$username = "<unknown>";
		if($username != "<unknown>"){
			$user    = $db->nextData();
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