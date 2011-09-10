<?php
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
?>		