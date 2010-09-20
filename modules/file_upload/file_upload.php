<?php

	require_once "includes/config.php";
/*
	function file_upload_boot($path, $module_details, $values) {
	}
*/

	function file_upload_deps($path, $module_details, $values = array()) {
		global $active_user;
		if ($active_user) {
			return show_uploaded_files($path, $module_details);
		}
	}

	function sort_file_list_result(&$user_files, $sort_dest, $sort_dir) {
		usort(
			$user_files,
			"sort_" . $sort_dir . "_" . $sort_dest
		);
	}

	function sort_asc_name($a, $b) {
		if ($a['file_name'] == $b['file_name']) {
			return 0;
		}
		return ($a['file_name'] < $b['file_name']) ? -1 : 1;
	}

	function sort_desc_name($a, $b) {
		if ($a['file_name'] == $b['file_name']) {
			return 0;
		}
		return ($a['file_name'] > $b['file_name']) ? -1 : 1;
	}

	function sort_asc_date($a, $b) {
		if (strtotime($a['file_uploaded_date']) == strtotime($b['file_uploaded_date'])) {
			return 0;
		}
		return (strtotime($a['file_uploaded_date']) < strtotime($b['file_uploaded_date'])) ? -1 : 1;
	}

	function sort_desc_date($a, $b) {
		if (strtotime($a['file_uploaded_date']) == strtotime($b['file_uploaded_date'])) {
			return 0;
		}
		return (strtotime($a['file_uploaded_date']) > strtotime($b['file_uploaded_date'])) ? -1 : 1;
	}

	function show_uploaded_files($path, $module_details, $values = array()) {
		$matches = array();
		$pattern = "/\/form\/(?P<primary>list|upload)\/(?<action>page)\/(?<page>\d+)(?<other>.*)?$/i";
		$num_matched = preg_match_all($pattern, $path, $matches);
		$current_page = 0;
		$other_stuff = '';
		if ($num_matched) {
			// turn on page navigation
			$page = $matches['page'][0];
			$current_page = ($page < 1) ? 0 : $page - 1;
			$other_stuff = $matches['other'][0];
		}
		$pattern = "/^\/sort\/(?P<sortdest>name|date)\/(?P<sortdir>asc|desc)$/i";
		$num_matches = preg_match_all($pattern, $other_stuff, $matches);
		$sort_dest = '';
		$sort_dir = '';
		if ($num_matches) {
			$sort_dest = $matches['sortdest'][0];
			$sort_dir = $matches['sortdir'][0];
		}
		global $active_user;
		if (!$active_user) {
			return array();
		}
		$result = array(
			"module"	=>	array(
				"current-page"	=>	$current_page + 1,
				"sort-direction"=>	($sort_dir == "asc") ? "desc" : "asc",
				"sort-date-dir"	=>	($sort_dest == 'date') ? 1 : 0,
				"sort-name-dir"	=>	($sort_dest == 'name') ? 1 : 0,
				"user_files"	=>	array(),
				"error"			=>	'',
				"file_pages"	=>	array()
			),
			"title"		=>	"Upload a file",
			"template"	=>	"template/uploaded_files.tpl"
		);
		$user_id = $active_user['user_id'];
		$count_query = <<<SQL
SELECT
	COUNT(*)
FROM
	client_files cf
WHERE
	cf.user_id = $user_id
SQL;
		$files_count = 0;
		$query = <<<SQL
SELECT
	cf.client_file_id,
	cf.user_id,
	cf.file_name,
	cf.file_uploaded_date,
	cf.file_hash
FROM
	client_files cf
WHERE
	cf.user_id = $user_id
ORDER BY
	cf.file_uploaded_date DESC
SQL;
		$files_res = db_query_per_page($query, $count_query, $current_page,
			$pages_count, $total_count, $files_count, $query_offset);
		if ($files_res) {
			$user_files = db_fetch_arrays($files_res);
			if (!empty($sort_dest) && !empty($sort_dir)) {
				sort_file_list_result($user_files, $sort_dest, $sort_dir);
			}
			foreach($user_files as $i => $user_file) {
				$user_file['file_link'] = "http://" . $_SERVER['HTTP_HOST'] . "/form/get/" . $user_file['file_hash'];
				$user_file['file_num'] = $query_offset + ++$i;
				array_push(
					$result['module']['user_files'],
					$user_file
				);
			}
			$result['module']['count'] = $files_count;
			$result['module']['total_count'] = $total_count;
		}
		for($i = 0; $i < $pages_count; $i++) {
			array_push(
				$result['module']['file_pages'],
				array(
					"page"		=>	$i + 1,
					"current"	=>	($i == ($current_page)) ? 0 : 1
				)
			);
		}
		return $result;
	}

	function file_upload_get($path, $module_details, $values = array()) {
		global $active_user;
		$matches = array();
		preg_match('/^\/form\/(?P<action>get|download)\/(.*)$/', $path, $matches);
		if (!empty($matches)) {
			$user_id = (isset($active_user) ? $active_user['user_id'] : 0);
			$action_taken = $matches['action'];
			if ($action_taken == 'get') {
				// check for comment module available
				$query = <<<SQL
SELECT
	m.*
FROM
	modules m
WHERE
	m.module_name = 'comments'
AND
	m.module_available = 1
SQL;
				$module_result = db_query($query);
				$file_hash = urlencode($matches[2]);
				$query = <<<SQL
SELECT
	cgc.comment_grant_id,
	cf.client_file_id,
	cf.user_id,
	ua.user_email_address,
	ua.user_first_name,
	ua.user_last_name,
	cf.file_name,
	cf.file_uploaded_date,
	cf.file_hash,
	cf.mime_type
FROM
	client_files cf
JOIN user_address ua
	ON ua.user_id = cf.user_id
LEFT JOIN
	client_grant_comments cgc
		ON cgc.client_file_id = cf.client_file_id
WHERE
	cf.file_hash = '$file_hash'
SQL;
				$result = db_fetch_array(db_query($query));
				return array(
					"module"	=>	array(
						"file-hash"				=>	$file_hash,
						"file-name"				=>	urldecode($result['file_name']),
						"file-size"				=>	file_get_readable_size(TEMP_FOLDER . $result['client_file_id'] . TEMP_FILE_EXT),
						"file-owner-details"	=>	get_html_user_details($result['user_id']),
						"owner-last-name"		=>	urldecode($result['user_last_name']),
						"owner-email"			=>	urldecode($result['user_email_address']),
						"file-mime"				=>	$result['mime_type'],
						"comments-owner"		=>	($result['user_id'] == $user_id) ? 1 : 0,
						"comments-available"	=>	isset($result['comment_grant_id'])
					),
					"title"		=>	"File comments",
					"template"	=>	"template/file_get.tpl"
				);
			}
			else if($action_taken == 'download') {
				if (isset($matches[2])) {
					$file_hash = urlencode($matches[2]);
					$query = <<<SQL
SELECT
	cf.client_file_id,
	cf.user_id,
	cf.file_name,
	cf.file_uploaded_date,
	cf.file_hash,
	cf.mime_type
FROM
	client_files cf
WHERE
	cf.file_hash = '$file_hash'
SQL;
					$result = db_query($query);
					if ($result) {
						$file_details = db_fetch_array($result);
						if (!empty($file_details)) {
							output_file($file_details);
						}
					}
				}
			}
		}
		if ($active_user) {
			return show_uploaded_files($path, $module_details);
		}
		else {
			// show upload form
		}
	}

	function file_upload_post($path, $module_details, $values = array()) {
		global $active_user;
		if ($active_user) {
			$user_id = $active_user['user_id'];
			if (!empty($_FILES)) {
				if ($_FILES['upload_file']['error'] == 0 ) {
					$target_path = TEMP_FOLDER . basename($_FILES['upload_file']['name']);
					if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target_path)) {
						$file_path_record = put_file_record_into_db($_FILES['upload_file']);
						copy($target_path, $file_path_record['file_path']);
						unlink($target_path);
						redirect_to_url("/form/list/");
					}
				}
				$error_result = show_uploaded_files($path, $module_details);
				$error = "";
				switch($_FILES['upload_file']['error']) {
					case 1:
					case 2:
						$error = "The uploaded file exceeds max filesize (" . ini_get("upload_max_filesize") . ").";
					break;
					default:
						$error = "The uploaded file was not uploaded due to internal problems (code: " . $_FILES['upload_file']['error'] . ").";
				}
				$error_result['module']['error'] = $error;
				return $error_result;
			}
			else {
				// process delete files
				foreach($values as $file_value_key => $file_value) {
					$matches = array();
					if (preg_match('/^file_(.+)+$/i', $file_value_key, $matches)) {
						$file_id = $matches[1];
						// check if file uploaded by current user
						$query = <<<SQL
SELECT
	cf.user_id
FROM
	client_files cf
WHERE
	cf.user_id = $user_id
AND
	cf.client_file_id = $file_id
SQL;
						$result = db_query($query);
						if($result) {
							// remove file from file system
							$target_file_name = TEMP_FOLDER . $file_id . TEMP_FILE_EXT;
							if (file_exists($target_file_name)) {
								unlink($target_file_name);
							}
							$query = <<<SQL
DELETE FROM client_files
WHERE client_file_id = $file_id
SQL;
							db_query($query);
							global $delete_file_hooks;
							if (!empty($delete_file_hooks)) {
								foreach ($delete_file_hooks as $delete_hook) {
									if (function_exists($delete_hook)) {
										call_user_func($delete_hook, $file_id);
									}
								}
							}
						}
					}
				}
				return show_uploaded_files($path, $module_details);
			}
		}
		else {
		}
	}

	function put_file_record_into_db($file_details) {
		global $active_user;
		$result = array(
			"file_link"	=>	"",
			"file_path"	=>	""
		);
		if ($active_user) {
			$user_id = $active_user["user_id"];
			$file_name = basename($file_details['name']);
			$file_hash = md5($file_name . $user_id . time());
			$mime_type = $file_details['type'];
			$query = <<<SQL
INSERT INTO client_files
VALUES (NULL, $user_id, '$file_name', NOW(), '$file_hash', '$mime_type')
SQL;
			$file_id = db_last_insert_id(db_query($query));
			$target_file_name = $file_id . TEMP_FILE_EXT;
			$result['file_path'] = TEMP_FOLDER . $target_file_name;
			$result['file_link'] = "http://" . $_SERVER['HTTP_HOST'] . "/form/get/" . $file_hash;
		}
		return $result;
	}

	function output_file($file_details) {
		cleanup_buffers(true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-Type: " . $file_details['mime_type']);
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=\"" . $file_details['file_name'] . "\"");
		readfile(TEMP_FOLDER . $file_details['client_file_id'] . TEMP_FILE_EXT);
		exit();
	}

	function file_get_readable_size($file_name) {
		if (file_exists($file_name)) {
			$file_size = filesize($file_name);
			$units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
			$c = 0; $p = 1;
			if(!$p && $p !== 0) {
				foreach($units as $k => $u) {
					if(($file_size / pow(1024, $k)) >= 1) {
						$r["bytes"] = $file_size / pow(1024, $k);
						$r["units"] = $u;
						$c++;
					}
				}
				return number_format($r["bytes"], 2) . " " . $r["units"];
			}
			else {
				return number_format($file_size / pow(1024, $p)) . " " . $units[$p];
			}
		}
		return '';
	}

?>
