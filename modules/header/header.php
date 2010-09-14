<?php

	function header_hook($type, $path, $module_details, $values = array()) {
		global $active_user;
		$email_address = '';
		$first_name = '';
		$last_name = '';
		$last_logged_date = 'None';
		$last_ip = 'None';
		if ($active_user) {
			$user_id = $active_user['user_id'];
			$query = <<<SQL
SELECT
	u.user_id,
	ua.user_email_address,
	ua.user_first_name,
	ua.user_last_name
FROM
	users u
JOIN user_address ua
	ON ua.user_id = u.user_id
WHERE
	u.user_id = $user_id
SQL;
			$result = db_fetch_array(db_query($query));
			if ($result) {
				$email_address = $result['user_email_address'];
				$first_name = $result['user_first_name'];
				$last_name = $result['user_last_name'];
			}
			$query = <<<SQL
SELECT
	s.session_id,
	s.session_start_date
FROM
	sessions s
WHERE s.user_id = $user_id
ORDER by s.session_id ASC
SQL;
			$result = db_query($query);
			if ($result) {
				$last_dates = db_fetch_arrays($result);
				$num = count($last_dates);
				if ($num > 1) {
					$prev_date_details = $last_dates[$num - 2];
					$last_logged_date = date($prev_date_details['session_start_date']);
				}
			}
			$session_name = $_COOKIE['__unique_session_key'];
			$session_details = sys_session_details($session_name);
			$session_id = 0;
			if (!empty($session_details)) {
				$session_id = $session_details['session_id'];
			}
			$query = <<<SQL
SELECT
	ch.client_ip_address
FROM
	client_history ch
WHERE
	ch.user_id = $user_id
AND
	ch.session_id = $session_id
SQL;
			$result = db_query($query);
			if ($result) {
				$last_ip_res = db_fetch_array($result);
				$last_ip = $last_ip_res['client_ip_address'];
			}
		}
		return array(
			"module"	=>	array(
				"header-title"			=>	HEADER_TEXT,
				"user-id"				=>	isset($active_user) ? $active_user['user_id'] : 0,
				"user-details"			=>	isset($active_user) ? get_html_user_details($active_user['user_id']) : '',
				"user-last-name"		=>	urldecode($last_name),
				"session-last-loggin"	=>	$last_logged_date,
				"session-last-ip"		=>	$last_ip
			),
			"title"		=>	"fff",
			"template"	=>	'template/header.tpl'
		);
	}

?>
