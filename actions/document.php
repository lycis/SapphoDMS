<?php
	if(!isset($_POST["id"])) die("Error: No document!");
	if(!isset($_POST["mode"])) die("Error: No mode!");
	
	if($_POST["mode"] != "read" &&
	   $_POST["mode"] != "write") die("Error: invalid mode");
	
	// Caption
	echo "<h3>Document #".$_POST["id"]."</h3>";
	
	// Buttons
	echo "<table><tr><td><input type=\"button\" value=\"edit\" id=\"document_edit\"/>";
	echo "</td><td><input type=\"button\" value=\"save\" id=\"document_save\"/>";
	echo "</td><td><input type=\"button\" value=\"cancel\" id=\"document_cancel\"/></td></tr></table>";
	echo "<input type=\"hidden\" id=\"document_mode\" value=\"".$_POST["mode"]."\"/>";
	echo "<input type=\"hidden\" id=\"document_id\" value=\"".$_POST["id"]."\"/>";

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
	}
	
	function getDocumentContent($did)
	{
		return "You are looking at object ".$did;
	}
?>