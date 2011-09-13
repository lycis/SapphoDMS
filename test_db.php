<?php
	require_once('lib/database.php');
	
	echo "<h1>Database Module Test</h1>";
	echo "<h3>Establishing connection</h3>";
	echo "<p>";
	$db = new DatabaseConnection('mysql', 'localhost', 'sappho', 'sappho');
	$db->setDebug(2);
	if($db->connect('test123') != 0)
		die("foo ".$db->lastError());
	echo "Connection OK";
	echo "</p>";
	
	echo "<h3>simple select</h3>";
	echo "<p>";
	$select = $db->select('object', array('object_id', 'object_name'));
	if($select) die("foo - $select: ".$db->lastError());
	
	$object = $db->nextData() or die("blaaa: ".$db->lastError());
	echo "</p>";
	
	echo "<h3>select with WHERE clause</h3>";
	echo "<p>";
	$select = $db->select('object', array('*'), 'object_id = 1');
	if($select) die("error - $select: ".$db->lastError());
	$object = $db->nextData() or die("error: ".$db->lastError());
	echo "</p>";
?>