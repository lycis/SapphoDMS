$(function(){
	function addAreaToUser(){
		$.post("actions/add_area_to_user.php",
				{area: $('#assoc_has_not').val(),
				 user: $('#associate_user_name').val()},
				function(data){
					var data_array = data.split(";");
					var state      = data_array[0];
					var msg        = data_array[1];
					if(state == "OK")
						location.reload();
					else
						alert("Failure: "+msg);
				}
		);
	}

	function removeAreaFromUser(){
		$.post("actions/remove_area_from_user.php",
				{area: $('#assoc_has').val(),
				 user: $('#associate_user_name').val()},
				function(data){
					var data_array = data.split(";");
					var state      = data_array[0];
					var msg        = data_array[1];
					if(state == "OK")
						location.reload();
					else
						alert("Failure: "+msg);
				}
		);
	}
	
	$('#assoc_add_area').click(addAreaToUser);
	$('#assoc_remove_area').click(removeAreaFromUser);
	$('#assoc_add_area').button();
	$('#assoc_remove_area').button();
});