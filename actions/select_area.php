<select id="area_selection">
<option>-- no area --</option>
<?php
	$request = "SELECT * from user_area WHERE user_area_uid = ".$_SESSION["uid"];
	$result  = mysql_query($request) or die("Could not get area data for user!");
	
	while($row = mysql_fetch_assoc($result)){
		$request = "SELECT area_name FROM area WHERE area_aid = ".$row["user_area_aid"];
		$result  = mysql_query($request) or die("Error on retrieving area data: ".mysql_error());
		$row     = mysql_fetch_assoc($result);
		echo "<option>".$row["area_name"]."</option>";
	}
?>
</select>