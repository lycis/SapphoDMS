$(function(){
	$("#add-object-dlg").dialog({
		resizable: false,
		height: 600,
		width: 800,
		modal: true,
		show: "explode",
		hide: "explode",
		buttons: {
			"Add": function() {
				var parent_id = $("#add-object-parentid").val();
				$.post("actions/add_new_object.php",
					   {parent: parent_id,
					    name: $("#add-object-name").val(),
						type: $("#add-object-type").val(),
						area: $("#area_selection").val()},
					   function(data){
							var data_array = data.split(";");
							var state      = data_array[0];
							var msg        = data_array[1];
							
							if(state == "NOK")
								alert("Failure - "+msg);
							location.reload();
					   });
				$(this ).dialog( "close" );
			},
			"Cancel": function() {
				$(this).dialog( "close" );
			}
		}
	});
	$("#fileview-dlg").treeview();
	$(".add-object-parent").click(function(){
		$("#add-object-parent-label").html("#"+$(this).attr("object_id"));
		$("#add-object-parentid").val($(this).attr("object_id"));
	});
});