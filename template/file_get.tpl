<div>
	<div id="file-details">
		<div id="file-details-title">File details:</div><div class="clear">&nbsp;</div>
		<div class="field-detail-row">
			<div class="field-detail-item">Name:</div><div class="field-detail-value"><a href="/form/download/#file-hash#">#file-name#</a></div>
		</div>
		<div class="field-detail-row">
			<div class="field-detail-item">Size:</div><div class="field-detail-value">#file-size#</div>
		</div>
		<div class="field-detail-row">
			<div class="field-detail-item">Uploaded by:</div><div class="field-detail-value">#file-owner-details#</div>
		</div>
		<div class="field-detail-row">
			<div class="field-detail-item">Mime type:</div><div class="field-detail-value">#file-mime#</div>
		</div>
	</div>

	<div class="clear">&nbsp;</div>

	<div class="file-details-download">
		<a href="/form/download/#file-hash#">Click here</a> to download file.
	</div>

	<div class="clear">&nbsp;</div>

	<if notzero=#comments-owner#>
	<if zero=#comments-available#>
	<div id="turn-on-comments">
		<form method="POST" action="/form/comment/allow/">
			<input type="hidden" value="/form/get/#file-hash#" name="back_url" />
			<input type="hidden" value="#file-hash#" name="file_hash" />
			<span class="note">NOTE:</span> Comments are turned off for that file. If you want, you can turn on comments. <input type="submit" value="Turn on comments" />
		</form>
	</div>
	</if>
	</if>
</div>
