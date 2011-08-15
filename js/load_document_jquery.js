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
		loadDocument(item.val());
		return false;
	});
});