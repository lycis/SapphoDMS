$(function() {
	function toggleDocument(){
		$("#document").toggle("highlight", {}, 500);
		$("#document_spacer").show("highlight", {}, 500);
		}
					 
	function loadDocument(did){
		//var old_id = $("#document_last_id").val();
		//	if(old_id == did)
		//		toggleDocument();
		 
			$("#document_last_id").val(did);
					 
			if($("#document").is(":hidden")) return false;
				
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
	
	function addNewObject(){
		$.post("actions/create_add_object_dlg.php",
			   {area: $("#area_selection").val()},
			   function (data){
					$("#free-for-stuff").html(data);
					$.getScript("js/add_new_object_dlg.js");
			   });
	}
	
	function showDeleteDialog(){
		$.post("actions/create_delete_object_dlg.php",
				{area: $("#area_selection").val()},
				function(data){
					$("#free-for-stuff").html(data);
					$.getScript("js/delete_object_dialog.js");
				});
	}
			
	$("#fileview").treeview({ 
								collapsed: true, 
								animated: "fast"
							});
	$("#document_last_id").val(-1);
	
	$(".document_link").click(function(){
		var item = $(this);
		loadDocument(item.attr("document_id"));
		return false;
	});
	
	$("#filetree-button-add-item").button();
	$("#filetree-button-add-item").click(function(){
		$("#fileview-dlg").remove();
		addNewObject();
		return false;
	});
	
	$("#filetree-button-delete-item").button();
	$("#filetree-button-delete-item").click(showDeleteDialog);
});