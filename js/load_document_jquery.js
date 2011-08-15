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
					state_id = data[0];
					msg      = data[1];
					$("#document_content").html(msg);
					if(state_id != "NOK")
						loadDocument(parseInt(state_id));
					return true;
				}
		);
	}
	
	function doCancel(did){
		$("#confirm-dialog").dialog({
				resizable: false,
				height: 200,
				width: 400,
				modal: true,
				buttons: {
					"Yes": function() {
						$( this ).dialog( "close" );
						unlockDocument(did);
					},
					"No": function() {
						$( this ).dialog( "close" );
					}
				}
			});
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
			   {id: did, content: instance.getData()},
			   function(data){
			   });
	}
	
	$("input:button").button();
	if($("#document_mode").val() == "read"){
		$("#document_save").hide();
		$("#document_cancel").hide();
		}
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
});