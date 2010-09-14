<?php

	require_once "includes/session.php";

	function client_history_deps($path, $module_details, $values = array()) {
		global $active_user;
		if ($active_user) {
			$user_id = $active_user['user_id'];
			$session_name = $_COOKIE['__unique_session_key'];
			if (!empty($session_name)) {
				$session_details = sys_session_details($session_name);
				$session_id = 0;
				if (!empty($session_details)) {
					$session_id = $session_details['session_id'];
				}
				$query = <<<SQL
SELECT
	ch.client_history_id,
	ch.user_id,
	ch.browser_name,
	ch.client_ip_address,
	ch.history_action,
	ch.session_id,
	ch.client_history_date
FROM
	client_history ch
WHERE
	ch.user_id = $user_id
AND
	ch.session_id = $session_id
ORDER BY
	ch.client_history_id DESC
LIMIT 1
SQL;
				$result = db_fetch_array(db_query($query));
				$browser = client_history_detect_browser();
				$ip = $_SERVER['REMOTE_ADDR'];
				if ($result) {
					$ch_id = $result['client_history_id'];
					$query = <<<SQL
UPDATE client_history
SET
	client_history_date = NOW(),
	history_action = '$path',
	browser_name = '$browser',
	client_ip_address = '$ip'
WHERE
	client_history_id = $ch_id
SQL;
					db_query($query);
				}
				else {
					$query = <<<SQL
INSERT INTO client_history
VALUES(NULL, $user_id, '$browser', '$ip', '$path', $session_id, NOW())
SQL;
					db_query($query);
				}
			}
		}
	}

	function client_history_detect_browser($user_agent = null) {
		$browser_name = 'Unknown';
		if ($user_agent == null) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
		$known_browsers = array(
			"firefox",
			"msie",
			"safari",
			"opera",
			"konqueror",
			"chrome"
		);
		$known_browsers_names = array(
			"firefox"	=>	"Mozilla Firefox",
			"msie"		=>	"Microsoft Internet Explorer",
			"safari"	=>	"Apple Safari",
			"opera"		=>	"Opera",
			"konqueror"	=>	"Konqueror",
			"chrome"	=>	"Google Chrome"
		);
		$pattern = "#(?<browser>" . join('|', $known_browsers) . ")[/ ]+(?<version>[0-9]+(?:.[0-9]+)+)?#i";
		$matches_count = preg_match_all($pattern, $user_agent, $matches);
		if ($matches_count) {
			$browser_name = $matches['browser'][0] . " " . (!empty($matches['version']) ? $matches['version'][0] : '');
		}
		return $browser_name;
	}

?>
