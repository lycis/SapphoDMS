<?php
	function insert_versioned_record($object)
	{
		// check if versioned data exists
		$request = "SELECT versioned_data_lnr FROM versioned_data WHERE versioned_data_id = ".$object["object_id"];
		$result  = mysql_query($request) or die("NOK;Could not verify versioned data existence - ".mysql_error());
		if(mysql_num_rows($result) < 1) $vlnr = 1;
		else
		{
			$request = "SELECT MAX(versioned_data_lnr) AS 'lnr' ".
					   "FROM versioned_data ".
					   "WHERE versioned_data_id = ".$object["object_id"];
			$result  = mysql_query($request) or die("NOK;Could not calculate version number.");
			$row     = mysql_fetch_assoc($result);
			$vlnr    = $row["lnr"]+1;
		}
		
		$request = "SELECT * FROM object_data WHERE object_data_id = ".$object["object_id"];
		$result  = mysql_query($request) or die("NOK;Error while versioning - ".mysql_error());
		$object_data = mysql_fetch_assoc($result);
		
		if($object["object_type"] = "D")
			$request = "INSERT INTO versioned_data(versioned_data_lnr, versioned_data_id, versioned_data_text, versioned_data_time, versioned_data_user) ".
			           "(SELECT $vlnr, ".$object["object_id"].", object_data_text, object_data_last_change, object_data_last_user FROM object_data ".
					   "WHERE object_data_id = ".$object["object_id"].")";
		else
			die("NOK;You can not change this type of document!");
		
		$result = mysql_query($request) or die("NOK;Could not create versioned record - ".mysql_error());
		return true;
	}
?>