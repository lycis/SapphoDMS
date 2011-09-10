<?php
	session_start();
	if(!isset($_POST["area"])) die("Please select a valid area!");
	
	echo "<div id=\"delete-object-dlg\" title=\"Delete object\" align=\"center\">";
	echo "  <table>";
	echo "    <tr><th colspan=\"2\">Choose object</th></tr>";
	echo "	  <tr><td colspan=\"2\"><p>Folders can only be deleted if they are empty.</p></td></tr>";
	echo "    <tr><td colspan=\"2\">";
	$_POST["mode"] = "delete-dialog";
	include "folderlist.php";
	echo "   </td></tr>";
	echo "  </table>";
	echo "<p>This object will be deleted:</p>";
	echo "<div id=\"delete-object-data\"></div>";
	echo "<input type=\"hidden\" id=\"add-object-parentid\"/>";
	echo "</div>";
	echo "<input type=\"hidden\" id=\"item-to-delete\"/>";
?>