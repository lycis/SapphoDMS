<?php
	$jquery_script = "<script> ".
					 " $(function() { ".
					 "		function toggleDocument(){".
					 "			$(\"#document\").toggle(\"highlight\", {}, 500);".
					 "			$(\"#document_spacer\").show(\"highlight\", {}, 500);".
					 "		}".
					 
					 "		function loadDocument(did){".
					 "			var old_id = $(\"#document_last_id\").val();".
					 "			if(old_id == did)".
					 "				toggleDocument();".
					 
					 "			$(\"#document_last_id\").val(did);".
					 
					 "			if($(\"#document\").is(\":hidden\")) return false;".
				
					 "			$(\"#document_content\").html('<p>Loading document...</p>');".
					 "			$.post(\"actions/document.php\", {id: did}, function(data){".
					 "				$(\"#document_content\").html(data);".
					 "				return true;".
					 "				}".
					 "			);".
					 "		}".
			
					 " 		$(\"#fileview\").treeview({ ".
					 "			collapsed: true, ".
					 "			animated: \"fast\" ".
					 "		}); ".
					 "		$(\"#document_last_id\").val(-1);".
					 "		$(\"#document\").hide();".
					 "		$(\".document_link\").click(function(){".
					 "			var item = $(this);".
					 "			loadDocument(item.attr(\"document_id\"));".
					 "			return false;".
					 "		});".
					 "});".
					 "</script>";
					 
	echo $jquery_script;
	
	if($_POST["area"] == "-- no area --") exit;
	echo "<div>";
	echo "<ul class=\"filetree\" id=\"fileview\">";
	echo 	"<li><span class=\"folder\">Folder 1</span>";
	echo		"<ul>";
	echo 			"<li><span class=\"file\"><a href=\"#\" class=\"document_link\" document_id=\"1\">Document 1.1</a></span></li>";
	echo		"</ul>";
	echo 	"</li>";
	echo "</ul>";
?>