<div>
	<form action="/form/upload/" method="POST">
	<script type="text/javascript" language="javascript">
	<!--
		window.onload = function() {
			var f_all = document.getElementById("files_all");
			if (f_all) {
				f_all.onclick = function() {
					if(!select_checkboxes(f_all.checked, document.getElementById('files_table'))) {
						alert("No any files were uploaded");
					}
				}
			}
		}

		function select_checkboxes(checked, rootElement) {
			var count = 0;
			if (rootElement && rootElement.childNodes.length > 0) {
				for (var i = 0; i < rootElement.childNodes.length; i++) {
					if (rootElement.childNodes[i].nodeName == 'INPUT' && rootElement.childNodes[i].type == 'checkbox') {
						count++;
						var obj = rootElement.childNodes[i];
						obj.checked = checked;
					}
					else if (rootElement.childNodes[i].childNodes.length > 0) {
						count += select_checkboxes(checked, rootElement.childNodes[i]);
					}
				}
			}
			return count;
		}

	-->
	</script>

	<if notempty=#error#>
	<div class="error_box">
		ERROR: #error#
	</div>
	</if>

	<table width="100%" cellspacing="0" cellpadding="0" class="files_table" id="files_table">
	<array @user_files@>
		<array:header>
		<tr class="table_header">
			<td><input type="checkbox" id="files_all" <if zero=#count#>disabled="true"</if>/></td>
			<td>#</td>
			<td><a class="sort-column<if notzero=#sort-name-dir#> sorted</if>" href="/form/list/page/#current-page#/sort/name/#sort-direction#">File name</a></td>
			<td><a class="sort-column<if notzero=#sort-date-dir#> sorted</if>" href="/form/list/page/#current-page#/sort/date/#sort-direction#">Uploaded date</a></td>
		</tr>
		</array:header>
		<array:body>
		<tr>
			<td><input type="checkbox" id="file_<array:value @client_file_id@/>" name="file_<array:value @client_file_id@/>" /></td>
			<td><array:value @file_num@/></td>
			<td><a href="<array:value @file_link@/>"><array:value @file_name@/></a></td>
			<td><array:value @file_uploaded_date@/></td>
		</tr>
		</array:body>
		<array:empty>
		<tr>
			<td colspan="5" class="null_value" align="center">No any files were uploaded by you.</td>
		</tr>
		</array:empty>
	</array>
	</table>

	<div>
		<div class="clear">&nbsp;</div>
		<input type="submit" value="Delete selected files" <if empty=#count#>disabled="true"</if>/>
	</div>

	</form>

	<div class="clear">&nbsp;</div>
	<array @file_pages@>
		<array:header>
			<div id="pages">
				Pages: 
		</array:header>
		<array:body>
			<if zero=@current@>
			<b><array:value @page@/></b>
			</if>
			<if notzero=@current@>
			<a href="/form/list/page/<array:value @page@/>"><array:value @page@/></a>
			</if>
		</array:body>
		<array:footer>
				<div id="pages-total"><b>#count#</b> file(s) shown from #total_count# files.</div>
			</div>
		</array:footer>
	</array>
	<div class="clear">&nbsp;</div>

	<form enctype="multipart/form-data" action="/form/upload/" method="POST">
		<div class="file_input_box"><input type="file" id="upload_file" name="upload_file" /></div>
		<div class="file_submit_box"><input type="submit" value="Upload a file" /></div>
		<div class="clear"></div>
	</form>

</div>
