<?php
	require_once "config.php";

	/**
	 *
	 */
	function _db_connect($url) {
		$url = parse_url($url);
		$url['user'] = urldecode($url['user']);
		$url['pass'] = isset($url['pass']) ? urldecode($url['pass']) : '';
		$url['host'] = urldecode($url['host']);
		$url['path'] = urldecode($url['path']);
		// Allow for non-standard MySQL port.
		if (isset($url['port'])) {
			$url['host'] = $url['host'] .':'. $url['port'];
		}
		$connection = @mysql_connect(
			$url['host'],
			$url['user'],
			$url['pass'],
			TRUE,
			2
		);
		if (!$connection || !mysql_select_db(substr($url['path'], 1))) {
			_db_error_page("",mysql_error());
		}
		// Force UTF-8.
		mysql_query('SET NAMES "utf8"', $connection);
		return $connection;
	}

	/**
	 *
	 */
	function db_set_active() {
		global $active_db;
		if (!defined(DB_URL)) {
			$active_db = _db_connect(DB_URL);
		}
	}

	/**
	 *
	 */
	function _db_error_page($query, $error) {
		cleanup_buffers(true);
		header ('HTTP/1.1 503 Service Unavailable');
		print "<pre>";
		print "Database connection settings are not correct. Please check.<br />";
		print "<b><u>ERROR</u></b>: " . $error . "<br />";
		print "<b><u>QUERY</u></b>: " . $query;
		print "</pre>";
		exit;
	}

	/**
	 *
	 */
	function db_query($query) {
		global $active_db;
		$result = mysql_query($query, $active_db);
		if (!$result) {
			_db_error_page($query, mysql_error($active_db));
		}
		return $result;
	}

	function db_query_per_page($query, $count_query, $page = 0, &$pages_count = 0, &$total_count = 0,
			&$rows_count = 0, &$query_offset = 0) {
		global $active_db;
		$result = mysql_query($count_query, $active_db);
		if (!$result) {
			_db_error_page($count_query, mysql_error($active_db));
		}
		$count_res = mysql_fetch_row($result);
		$total_count = $count_res[0];
		$files_count = FILE_LIST_PER_PAGE;
		$query_offset = $files_count * $page;
		if ($query_offset > $total_count) {
			$query_offset = floor($total_count / $files_count) * $files_count;
		}
		else if($query_offset == $total_count) {
			$query_offset = 0;
		}
		$pages_count = ceil($total_count / $files_count);
		$query .= <<<SQL

LIMIT $files_count OFFSET $query_offset
SQL;
		$result = db_query($query);
		$rows_count = mysql_num_rows($result);
		return $result;
	}

	function db_fetch_object($result) {
		if ($result) {
			return mysql_fetch_object($result);
		}
	}

	function db_fetch_array($result) {
		if ($result) {
			return mysql_fetch_array($result, MYSQL_ASSOC);
		}
	}

	function db_fetch_arrays($result) {
		if ($result) {
			$value = array();
			while($item = mysql_fetch_array($result, MYSQL_ASSOC)) {
				array_push($value, $item);
			}
			return $value;
		}
	}

	function db_last_insert_id($result) {
		global $active_db;
		if ($result) {
			return mysql_insert_id($active_db);
		}
		
	}
?>
