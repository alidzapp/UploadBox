<div>
	<form method="POST" action="/account/proceed/#security_token#">
		Your email address: <span class="simple_label">#email_address#</span>. Please proceed registration
		<div class="clear">&nbsp;</div>
		<div class="input_row">
			<div class="input_label">
				<label for="register_first_name" class="#field-error-class#" style="width: 150px;">First name: </label>
				<input type="text" id="register_first_name" name="register_first_name" />
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="input_row">
			<div class="input_label">
				<label for="register_last_name" class="#field-error-class#" style="width: 150px;">Last name: </label>
				<input type="text" id="register_last_name" name="register_last_name" />
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="input_row">
			<div class="input_label">
				<label for="register_password" class="#field-error-class#" style="width: 150px;">Password: </label>
				<input type="password" id="register_password" name="register_password" />
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="input_row">
			<div class="input_label">
				<label for="register_confirm_password" class="#field-error-class#" style="width: 150px;">Confirm password: </label>
				<input type="password" id="register_confirm_password" name="register_confirm_password" />
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<if notempty=#error#>
		<div class="error-box">ERROR: #error#</div>
		<div class="clear">&nbsp;</div>
		</if>
		<div>
			<div class="input_field" style="width: 155px;">&nbsp;</div><input type="submit" value="Register" />
		</div>
	</form>
</div>
