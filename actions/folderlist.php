<!-- JQUERY -->
<script>
	$(function() {
		$("#fileview").treeview({
									collapsed: true,
									animated: "fast"
								});
	});
</script>

<!-- LAYOUT -->
<div>
	<ul class="filetree" id="fileview">
		<li><span class="folder">Folder 1</span>
			<ul>
				<li><span class="file"><a href="#" class="document_link" document_id="1">Document 1.1</a></span></li>
				<li><span class="folder">Folder 1.2</span>
					<ul>
						<li><span class="file"><a href="#" class="document_link" document_id="2">Document 1.2.1</a></span></li>
					</ul>
				</li>
				<li><span class="file"><a href="#" class="document_link" document_id="3">Document 1.3</a></span></li>
			</ul>
		</li>
		<li><span class="folder">Folder 2</span>
			<ul>
				<li><span class="file"><a href="#" class="document_link" document_id="4">Document 2.1</a></span></li>
			</ul>
		</li>
	</ul>
</div>