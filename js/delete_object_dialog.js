$(function(){
	$("#delete-object-dlg").dialog({
		resizable: false,
		height: 600,
		width: 800,
		modal: true,
		show: "explode",
		hide: "explode",
		buttons: {
			"Confirm": function(){
				$.post("actions/delete_object.php",
				       {oid: $("#item-to-delete").val()},
					   function(data){
							var data_array = data.split(";");
							var state      = data_array[0];
							var msg        = data_array[1];
							
							if(state == "NOK")
								alert("Failure - "+msg);
							location.reload();
					   });
			},
			"Cancel": function() {
				$(this).dialog( "close" );
			}
		}
	});
	
	$("#fileview-dlg").treeview({
		collapsed: true,
		animated: "fast"});
		
	$(".delete-item").click(function(){
		$("#delete-object-data").html("<p>#"+$(this).attr("object_id")+" "+$(this).attr("object_name"));
		$("#item-to-delete").val($(this).attr("object_id"));
	});
});