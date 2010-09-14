<div>
	<div class="box_title">
		<div class="note_title">
			Please, provide us your email address to proceed register. You'll receive an email with validation link
		</div>
	</div>
	<form method="POST" action="/account/register/">
	<div class="input_row">
		<div class="input_label">
			<label for="register_email" class="#field-error-class#">Email: </label>
			<input type="text" id="register_email" name="register_email" />
		<if notempty=#error#>
			<span class="error-note">#error#</span>
		</if>
		</div>
	</div>
	<div class="clear">&nbsp;</div>
	<div>
		<div class="input_field">&nbsp;</div><input type="submit" value="Send email" />
	</div>
	</form>
</div>
