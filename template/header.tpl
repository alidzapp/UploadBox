<div>
	<div id="header-title">
		#header-title#
		<div id="header-subtitle">File upload service</div>
	</div>
	<div id="header-menu">
		<if zero=#user-id#><a href="/user/login/">Login</a></if>
		<div id="user-email">
			<if notzero=#user-id#>
				<a href="/form/list/">File list</a> | <a href="/user/logout/">Logout</a> [<b>#user-details#</b>]
			</if>
		</div>
		<if notzero=#user-id#>
		<div id="user-last-loggin">Your last loggin time <b>#session-last-loggin#</b> from an <b>#session-last-ip#</b></div>
		</if>
	</div>
</div>
<div class="clear">&nbsp;</div>
