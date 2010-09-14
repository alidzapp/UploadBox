<div>
	<div id="comments-tree">
		#comments-content#
	</div>
	<div class="clear">&nbsp;</div>

	<a href="#" onclick="CommentsControl.answer('comment-form-wrapper')">Add a comment</a>

	<div id="comment-form-wrapper">
	<form action="/form/comment/" method="POST" id="comment-form">
		<input type="hidden" id="comment_file_id" name="comment_file_id" value="#file_id#" />
		<input type="hidden" id="comment_parent_id" name="comment_parent_id" value="0" />
		<input type="hidden" name="back_url" value="/form/get/#file_hash#" />
		<div>
			<div class="login_field"><label for="comment_value">Your Comment: </label></div>
			<textarea id="comment_value" name="comment_value" class="flat-style-control"></textarea>
		</div>
		<div class="clear">&nbsp;</div>
		<div>
			<div class="login_field">&nbsp;</div><input type="submit" value="Add comment" />
		</div>
	</form>
	</div>
</div>
