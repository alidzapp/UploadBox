<?php

	require_once "db.php";

	function url_process($path = null) {
		global $final_url;

		$final_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if (!isset($path)) {
			$path = $_SERVER['REQUEST_URI'];
		}
		$path = trim($path);
		if ($path == '/') {
			$path = DEFAULT_URI;
		}
		$path = urldecode($path);
		$module_details = is_url_present($path);
		module_bootstrap($path, $module_details);
	}

	function is_url_present($path) {
		global $url_id;
		$query = <<<SQL
SELECT
	u.url_id,
	u.url_path,
	m.module_id,
	m.module_path,
	m.module_name,
	m.module_description,
	m.module_order
FROM url u
JOIN modules m ON u.module_id = m.module_id
WHERE
	m.module_available = 1
AND
	trim('$path') LIKE REPLACE(u.url_path,'*','%')
SQL;
		$result = db_fetch_array(db_query($query));
		if (!$result) {
			send_not_found($path);
		}
		$url_id = $result['url_id'];
		return $result;
	}

	function send_not_found($path) {
		global $final_url;
		cleanup_buffers(true);
		header ('HTTP/1.1 503 Service Unavailable');
		print "<pre>";
		print "URL <b><u><a href='$final_url'>$final_url</a></u></b> not found.<br/>Please check the URL first.<br />";
		print "</pre>";
		exit;
	}

	function redirect_to_url($url) {
		cleanup_buffers(true);
		header("Location: $url");
	}

?>
