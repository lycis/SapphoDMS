$(function(){
	function loadDocument(){
			var did = $("#document_id").val();
			$("#document_content").html('<p>Loading document...</p>');
			
			var instance = CKEDITOR.instances["document_"+did];
			if(instance)
			{
				CKEDITOR.remove(instance);
			}
						
			$.post("actions/document.php",
					{mode: "read", id: did}, 
					function(data){
						$("#document_content").html(data);
						$.getScript("js/load_document_jquery.js");
						return true;
					}
			);
	}
	
	function showVersion(){
		var did = $("#document_id").val();
		var vnr = $(this).attr("version");
		
		$("#document_content").html("<p>Recalling version "+vnr+"...");
		$.post("actions/document.php",
		       {id: did, mode: "show_version", version: vnr},
			   function(data){
					$("#document_content").html(data);
					$.getScript("js/load_document_jquery.js");
			   });
	}
	
	
	function restoreVersion(){
		var did = $("#document_id").val();
		var vnr = $(this).attr("version");
		$("#free-for-stuff").html("<p>Do you really want to roll back to version "+vnr+"?");
		$("#free-for-stuff").dialog({
			title: "Restore version",
			resizable: false,
			height: 170,
			width: 350,
			modal: true,
			show: "explode",
			hide: "explode",
			buttons:{
				"Yes": function(){
					$.post("actions/restore_version.php",
				       {id: did,
					    version: vnr},
					   function(data){
							alert(data);
							var data_array = data.split(";");
							var state      = data_array[0];
							var msg        = data_array[1];
							
							if(state == "NOK")
								alert("Failure - "+msg);
							else
								alert("Rollback successfull!");
							location.reload();
					   });
				},
				"No": function(){$(this ).dialog( "close" );}
			}
		});
	}

	$(".document_show_version").button();
	$(".document_restore_version").button();
	$("#document_show").button();
	
	$("#document_show").click(loadDocument);
	$(".document_show_version").click(showVersion);
	
	$(".document_restore_version").click(restoreVersion);
});