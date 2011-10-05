<select id="area_selection">
<option>-- no area --</option>
<?php
	$where = "user_area_uid = ".$_SESSION["uid"];
	if($sdbc->select('user_area', '*', $where))
		die("Could not get area data for user!");
	echo $sdbc->rowCount();
	
	$lastresult = $sdbc->getLastResult();
	while($row = $sdbc->nextData($lastresult)){
		$where = "area_aid = ".$row["user_area_aid"];
		if($sdbc->select('area', 'area_name', $where))
			die("Error on retrieving area data: ".$sdbc->lastError());
		$srow     = $sdbc->nextData();
		echo "<option>".$srow["area_name"]."</option>";
	}
?>
</select>
