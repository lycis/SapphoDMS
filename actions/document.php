<?php
	session_start();
	include("../config.php");
	$db_conn = mysql_connect($db_host,$db_user,$db_password) or die("The database host is not available!");
	mysql_select_db($db_name) or die("The database is not accessible!");
	
	if(!isset($_POST["id"])) die("Error: No document!");
	if(!isset($_POST["mode"])) die("Error: No mode!");
	
	if($_POST["mode"] != "read" &&
	   $_POST["mode"] != "write") die("Error: invalid mode");
	
	// Get data
	$request = "SELECT * FROM object WHERE object_id = ".$_POST["id"];
	$result  = mysql_query($request) or die("Document does not exist.");
	$row     = mysql_fetch_assoc($result);
	
	// Caption
	echo "<h3>".$row["object_name"]."</h3>";
	
	// Buttons
	echo "<table><tr><td><input type=\"button\" value=\"edit\" id=\"document_edit\"/>";
	echo "</td><td><input type=\"button\" value=\"save\" id=\"document_save\"/>";
	echo "</td><td><input type=\"button\" value=\"cancel\" id=\"document_cancel\"/></td></tr></table>";
	echo "<input type=\"hidden\" id=\"document_mode\" value=\"".$_POST["mode"]."\"/>";
	echo "<input type=\"hidden\" id=\"document_id\" value=\"".$_POST["id"]."\"/>";
	echo "<input type=\"hidden\" id=\"document_cancel\" value=\"0\"/>";

	if($_POST["mode"] == "read")
	{
		echo "<hr/>";
		echo getDocumentContent($_POST["id"]);
		echo "<hr/>";
	}
	else if($_POST["mode"] == "write")
	{
		include("ckeditor/ckeditor.php");
		// Include the CKEditor class.
		include_once "ckeditor/ckeditor.php";

		// Create a class instance.
		$CKEditor = new CKEditor();

		// Path to the CKEditor directory.
		$CKEditor->basePath = '/ckeditor/';

		// Set global configuration (used by every instance of CKEditor).
		$CKEditor->config['width'] = $_POST["width"];
		$CKEditor->config['height'] = $_POST["height"];

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
	
	function getDocumentContent($did)
	{
		$request = "SELECT object_type FROM object WHERE object_id = $did";
		$result  = mysql_query($request) or die("Error while loading document: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested document does not exist.");
		
		$row = mysql_fetch_assoc($result);
		$oid = $row["object_type"];
		if($oid != "D") die("This document can not be displayed!");
		
		$request = "SELECT object_data_text FROM object_data WHERE object_data_id = $did";
		$result  = mysql_query($request) or die("Error while loading document data: ".mysql_error());
		if(mysql_num_rows($result) < 1) die("Requested document does not have any data.");
		$row = mysql_fetch_assoc($result);
		
		return $row["object_data_text"];
	}
?>