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

	$(".document_show_version").button();
	$(".document_restore_version").button();
	$("#document_show").button();
	
	$("#document_show").click(loadDocument);
	$(".document_show_version").click(showVersion);
});