<?php

	require_once "includes/session.php";

	function user_login_boot($path, $module_details, $values) {
		global $active_user;
		clear_cookies_if_needed($path);
		user_login_active_user();
		return array();
	}

	function clear_cookies_if_needed($path) {
		global $active_user;
		$matches = array();
		preg_match('/^\/user\/logout\//', $path, $matches, PREG_OFFSET_CAPTURE);
		if (!empty($matches)) {
			$active_user = null;
			clear_cookies();
		}
	}

	function user_login_deps($path, $module_details, $values = array()) {
		global $active_user;
		global $url_id;
		user_login_active_user();
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
JOIN
	user_address ua
		ON ua.user_id = u.user_id
WHERE
	u.user_id = $user_id
SQL;
			$user = db_fetch_array(db_query($query));
			return array(
				"module"		=>	array(
					"email_address"	=>	urldecode($user['user_email_address']),
					"first_name"	=>	urldecode($user['user_first_name']),
					"last_name"		=>	urldecode($user['user_last_name']),
					"user_id"		=>	$user['user_id']
				),
				"title"			=>	"",
				"template"		=>	"template/login_details.tpl"
			);
		}
		$query = <<<SQL
SELECT
	au.*
FROM
	authorized_urls au
WHERE
	au.url_id = $url_id
SQL;
		$result = db_fetch_array(db_query($query));
		if ($result) {
			return array(
				"module"	=>	array(),
				"title"		=>	"",
				"template"	=>	"template/login.tpl"
			);
		}
	}

	function user_login_active_user() {
		global $active_user;

		if (isset($_COOKIE['__unique_session_key'])) {
			$active_user = sys_session_details($_COOKIE['__unique_session_key']);
		}
		else {
			$active_user = null;
		}
	}

	function clear_cookies() {
		unset($_COOKIE['__unique_session_key']);
		setcookie('__unique_session_key', '', time() - 3600, "/");
	}

	function user_login_get($path, $module_details, $values = array()) {
		global $active_user;
		clear_cookies_if_needed($path);
		user_login_active_user();
		if ($active_user) {
			$user = get_user_details($active_user['user_id']);
			return array(
				"module"	=>	array(
					"email_address"	=>	urldecode($user['user_email_address'])
				),
				"title"		=>	"Welcome",
				"template"	=>	"template/login_details.tpl"
			);
		}
		else {
			return array(
				"module"	=>	array(
					"last_email"			=>	"",
					"last_password"			=>	"",
					"login-email-class"		=>	"",
					"login-password-class"	=>	""
				),
				"title"		=>	"Introduce yourself to system",
				"template"	=>	"template/login_box.tpl"
			);
		}
	}

	function load_user($email, $password) {
		global $active_user;
		$password = md5(urlencode($password));
		$email = urlencode($email);
		$query = <<<SQL
SELECT
	u.user_id
FROM
	users u
JOIN
	user_address ua
		ON ua.user_id = u.user_id
WHERE
	u.user_password = '$password'
AND
	ua.user_email_address = '$email'
SQL;
		$result = db_fetch_array(db_query($query));
		if ($result) {
			$sid = sys_session_start($result['user_id']);
			$_COOKIE['__unique_session_key'] = $sid;
			setcookie('__unique_session_key', $sid, time() + 3600 * 24 * 1000, "/");
			return true;
		}
		else {
			return false;
		}
	}

	function user_login_post($path, $module_details, $values = array()) {
		if (empty($values)) {
			// Should not get there.
		}
		else {
			$logged_email = $values['login_email'];
			$logged_password = $values['login_password'];
			if (load_user($logged_email, $logged_password)) {
				redirect_to_url("/form/list/");
			}
			else {
				return array(
					"module"	=>	array(
						"login-email-class"		=>	"login-error-field",
						"login-password-class"	=>	"login-error-field",
						"last_email"			=>	$values['login_email'],
						"last_password"			=>	$values['login_password']
					),
					"title"		=>	"Loggin result",
					"template"	=>	"template/login_box.tpl"
				);
			}
		}
	}

?>
