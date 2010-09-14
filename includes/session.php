<?php
	function session_secure_id() {
		$alpha = array
		(
			'A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e',
			'F', 'f', 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j',
			'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o',
			'P', 'p', 'Q', 'q', 'R', 'r', 'S', 's', 'T', 't',
			'U', 'u', 'V', 'v', 'W', 'w', 'X', 'x', 'Y', 'y',
			'Z', 'z'
		);
		$tmp = array();
		for ($i = 0; $i < rand(10, 20); $i++) {
			$tmp[] = $alpha[rand(0, count($alpha) - 1)];
			$tmp[] = rand(0, 9);
		}
		shuffle($tmp);
		return implode("", $tmp);
	}

	function sys_session_start($user_id) {
		$sid = session_secure_id();
		$query = <<<SQL
INSERT INTO
	sessions
VALUES
(
	NULL,
	$user_id,
	'$sid',
	NOW()
)
SQL;
		db_query($query);
		return $sid;
	}

	function sys_session_close($session_id) {
		$query = <<<SQL
DELETE
FROM
	sessions
WHERE
	session_value = '$session_id'
SQL;
		return db_query($query);
	}

	function sys_session_details($session_id) {
		$query = <<<SQL
SELECT
	u.user_id,
	s.session_id
FROM
	sessions s
JOIN
	users u
		ON u.user_id = s.user_id
WHERE
	s.session_value = '$session_id'
SQL;
		return db_fetch_array(db_query($query));
	}

	function get_user_details($user_id) {
		$query = <<<SQL
SELECT
	u.user_id,
	ua.user_email_address,
	ua.user_first_name,
	ua.user_last_name
FROM
	users u
JOIN
	user_address ua
		ON ua.user_id = u.user_id
WHERE
	u.user_id = $user_id
SQL;
		return db_fetch_array(db_query($query));
	}

	function get_html_user_details($user_id, $template = 'template/user_details.tpl') {
		if (file_exists($template)) {
			$user_details = get_user_details($user_id);
			if (!empty($user_details)) {
				return show_template_wrapper(
					file_get_contents('template/blank.tpl'),
					array(
						array(
							"module"	=>	array(
								"user-profile-first-name"	=>	urldecode($user_details['user_first_name']),
								"user-profile-last-name"	=>	urldecode($user_details['user_last_name']),
								"user-profile-id"			=>	$user_details['user_id']
							),
							"template"	=>	$template
						)
					)
				);
			}
		}
		return '';
	}


?>
