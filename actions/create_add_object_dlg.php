<?php
	session_start();
	if(!isset($_POST["area"])) die("Please select a valid area!");
	
	echo "<div id=\"add-object-dlg\" title=\"Add new object\" align=\"center\">";
	echo "  <table>";
	echo "    <tr><th>Name:</th><td><input type=\"text\" id=\"add-object-name\" width=\"255\" length=\"255\"/></td>";
	echo "        <th>Type:</th><td><select id=\"add-object-type\"><option value=\"F\">Folder</option><option value=\"D\">Document</option></select></td></tr>";
	echo "    <tr><th colspan=\"2\">Choose parent folder</th></tr>";
	echo "    <tr><td colspan=\"2\">";
	$_POST["mode"] = "dialog";
	include "folderlist.php";
	echo "   </td></tr>";
	echo "   <tr><th>Selected Parent:</th><td><div id=\"add-object-parent-label\"></div></td></tr>";
	echo "  </table>";
	echo "<input type=\"hidden\" id=\"add-object-parentid\"/>";
	echo "</div>";
?>