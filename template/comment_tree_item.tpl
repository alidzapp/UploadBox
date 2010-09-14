<div class="comment-tree-item" style="margin-left: #padding-left#px;"
	onmouseover="CommentsControl.mouseOver('hover-control-#comment-id#')"
	onmouseout="CommentsControl.mouseOut('hover-control-#comment-id#')">
	<div class="comment-tree-item-value">
		#comment-content#
		<div id="hover-control-#comment-id#" class="comments-hover">
			<a href="#" onclick="CommentsControl.reply('comment-reply-form-#comment-id#','comment_parent_#comment-id#', #comment-id#)">Reply</a></div>
	</div>
	<div class="comment-tree-item-footer">Posted by <b>#comment-owner-details#</b> at #comment-date#</div>
	<div class="comment-reply-form" id="comment-reply-form-#comment-id#">
		<form action="/form/comment/reply/" method="POST" id="comment-form-#comment-id#">
			<input type="hidden" id="comment_file_id" name="comment_file_id" value="#comment-file-id#" />
			<input type="hidden" id="comment_parent_#comment-id#" name="comment_parent_id" value="#comment-id#" />
			<input type="hidden" name="back_url" value="/form/get/#comment-file-hash#" />
			<if zero=#user-id#>
			<div>
				<div class="login_field"><label for="comment_name">Your Name: </label></div>
				<input type="text" id="comment_name" name="comment_name" class="flat-style-control" />
			</div>
			<div class="clear">&nbsp;</div>
			</if>
			<div>
				<div class="login_field"><label for="comment_value">Your Comment: </label></div>
				<textarea name="comment_value" class="flat-style-control"></textarea>
			</div>
			<div class="clear">&nbsp;</div>
			<div>
				<div class="login_field">&nbsp;</div><input type="submit" value="Add comment" />
			</div>
		</form>
	</div>
</div>
