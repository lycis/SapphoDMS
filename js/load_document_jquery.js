$(function(){	
	function editDocument(did){		 		
			$("#document_content").html('<p>Loading document...</p>');
			
			var instance = CKEDITOR.instances["document_"+did];
			if(instance)
			{
				CKEDITOR.remove(instance);
			}

			var content_height = window.innerHeight-500;
			if(content_height < 350) content_height = 350;
						
			$.post("actions/document.php",
					{mode: "write", id: did, width: $("#document_content").width(), height: content_height}, 
					function(data){
						$("#document_content").html(data);
						$.getScript("js/load_document_jquery.js");
						$("#document_save").show();
						$("#document_cancel").show();
						$("#document_edit").hide();
						return true;
					}
			);
	}
	
	function loadDocument(did){		
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
	
	function unlockDocument(did){
		$("#document_content").html('<p>Unlocking document...</p>');
						
		$.post("actions/document.php",
				{mode: "unlock", id: did}, 
				function(data){
					var data_array = data.split(";");
					state_id = data_array[0];
					msg      = data_array[1];

					$("#document_content").html(msg);
					if(state_id != "NOK")
						loadDocument(parseInt(state_id));
					return true;
				}
		);
	}
	
	function doCancel(did){
		$("#confirm-dialog").dialog("open");
		return true;
	}
	
	function saveDocument(){
		var did = $("#document_id").val();
		var instance = CKEDITOR.instances["document_"+did];
		
		if(!instance){
			alert("Error: No editor available!");
			return true;
		}
		
		$.post("actions/save_document.php",
			   {id: $("#document_id").val(), 
			    content: instance.getData()},
			   function(data){
					var data_array = data.split(";");
					var state      = data_array[0];
					var msg        = data_array[1];
					
					if(state == "NOK")
						alert("Failure: "+msg);
					else
						alert(msg);
			   });
	}
	
	function showHistory(){
		var did = $("#document_id").val();
		$.post("actions/document.php",
				{id: did, mode: "history"},
				function(data){
					$("#document_content").html(data);
					$.getScript("js/load_history.js");
				});
	}
	
	function deleteDocument(){
		$("#free-for-stuff").html("<p>Do you really want do delete this document?</p>");
		$("#free-for-stuff").dialog({
			title: "Delete document",
			resizable: false,
			height: 170,
			width: 350,
			modal: true,
			show: "explode",
			hide: "explode",
			buttons:{
				"Yes": function(){
					$.post("actions/delete_object.php",
				       {oid: $("#document_id").val()},
					   function(data){
							var data_array = data.split(";");
							var state      = data_array[0];
							var msg        = data_array[1];
							
							if(state == "NOK")
								alert("Failure - "+msg);
							location.reload();
					   });
				},
				"No": function(){$(this ).dialog( "close" );}
			}
		});
	}
	
	$("input:button").button();
	
	$("#document_edit").click(function(){
		var item = $("#document_id");
		editDocument(item.val());
		return false;
	});
	$("#document_cancel").click(function(){
		var item = $("#document_id");
		doCancel(item.val());
		return false;
	});
	$("#document_save").click(saveDocument);
	$("#confirm-dialog").hide();
	$("#confirm-dialog").dialog({
		autoOpen: false,
		resizable: false,
		height: 200,
		width: 400,
		modal: true,
		show: "explode",
		hide: "explode",
		buttons: {
			"Yes": function() {
				$(this ).dialog( "close" );
				unlockDocument($("#document_id").val());
			},
			"No": function() {
				$(this ).dialog( "close" );
			}
		}
	});
	$("#document_history").button();
	$("#document_history").click(showHistory);
	
	if($("#document_mode").val() == "read"){
		$("#document_save").hide();
		$("#document_cancel").hide();
	}
	
	if($("#document_mode").val() == "write"){
		$("#document_edit").hide();
		$("#document_delete").click(deleteDocument);
	}
	
	if($("#document_mode").val() == "read"){
		$("#document_delete").hide();
	}
});