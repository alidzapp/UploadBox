<?php

	function account_get($path, $module_details, $values = array()) {

		$pattern = "/account\/(?P<action>register|verify|proceed)\/(?P<key>[^\/].*)?/i";
		$num_matches = preg_match_all($pattern, $path, $matches);
		if ($num_matches) {
			$action = $matches['action'][0];
			if ($action == 'register') {
				return array(
					"module"	=>	array(
						"field-error-class"	=>	"",
						"error"				=>	""
					),
					"title"		=>	"Registration process: email validation",
					"template"	=>	"template/account_register.tpl"
				);
			}
			else if($action == 'verify') {
				$reg_key = stripslashes(trim($matches['key'][0]));
				$query = <<<SQL
SELECT
	rk.registration_id,
	rk.registration_email
FROM
	registration_keys rk
WHERE
	rk.registration_key = '$reg_key'
SQL;
				$result = db_fetch_array(db_query($query));
				if ($result) {
					redirect_to_url("/account/proceed/$reg_key");
					exit();
				}
				else {
					return account_print_message("The security token doesn't exists.");
				}
			}
			else if($action == 'proceed') {
				$reg_key = stripslashes(trim($matches['key'][0]));
				$query = <<<SQL
SELECT
	rk.registration_id,
	rk.registration_email
FROM
	registration_keys rk
WHERE
	rk.registration_key = '$reg_key'
SQL;
				$result = db_fetch_array(db_query($query));
				if ($result) {
					return array(
						"module"	=>	array(
							"security_token"	=>	"$reg_key",
							"email_address"		=>	urldecode($result['registration_email']),
							"error"				=>	""
						),
						"title"		=>	"Registration process: provide us your details",
						"template"	=>	"template/account_register_details.tpl"
					);
				}
				else {
					return account_print_message("The security token doesn't exists.");
				}
			}
		}
	}

	function account_post($path, $module_details, $values = array()) {
		$pattern = "/account\/(?P<action>register|proceed)\/(?P<key>[^\/].*)?/i";
		$num_matches = preg_match_all($pattern, $path, $matches);
		if ($num_matches) {
			$action = $matches['action'][0];
			if ($action == 'register') {
				if (!isset($values['register_email'])) {
					return array(
						"module"	=>	array(
							"error"				=>	"Email address should not be empty!",
							"field-error-class"	=>	"login-error-field"
						),
						"title"		=>	"Registration process: email validation",
						"template"	=>	"template/account_register.tpl"
					);
				}
				$email_address = urlencode(trim($values['register_email']));
				if (empty($email_address)) {
					return array(
						"module"	=>	array(
								"error"				=>	"Email address should not be empty!",
							"field-error-class"	=>	"login-error-field"
						),
						"title"		=>	"Registration process: email validation",
						"template"	=>	"template/account_register.tpl"
					);
				}
				else {
					$query = <<<SQL
SELECT
	ua.user_id
FROM
	user_address ua
WHERE
	ua.user_email_address = '$email_address'
SQL;
					$result = db_fetch_array(db_query($query));
					if ($result) {
						return array(
							"module"	=>	array(
								"error"				=>	"Email address is already in use!",
								"field-error-class"	=>	"login-error-field"
							),
							"title"		=>	"Registration process: email validation",
							"template"	=>	"template/account_register.tpl"
						);
					}
					else {
						$reg_key = account_generate_key();
						$query = <<<SQL
SELECT
	rk.registration_id
FROM
	registration_keys rk
WHERE
	rk.registration_email = '$email_address'
SQL;
						$result = db_fetch_array(db_query($query));
						if ($result) {
							return array(
								"module"	=>	array(
									"error"				=>	"Email address is already have receive invitation!",
									"field-error-class"	=>	"login-error-field"
								),
								"title"		=>	"Registration process: email validation",
								"template"	=>	"template/account_register.tpl"
							);
						}
						else {
							$query = <<<SQL
INSERT INTO registration_keys
VALUES(NULL, '$email_address', NOW(), '$reg_key')
SQL;
							db_query($query);
						}
						$secure_link = "http://" . $_SERVER['HTTP_HOST'] . "/account/verify/" . $reg_key;
						$message_content = show_template_wrapper(
							file_get_contents("template/blank.tpl"),
							array(array(
								"module"	=>	array(
									"secure_link"	=>	$secure_link
								),
								"template"	=>	"template/account_register_mail.tpl"
							))
						);
						$message_sent = account_send_mail('UploadBox.com registration: email validation',
							urldecode($email_address), $message_content);
						return account_print_message(($message_sent == 1)
							? ("Message sent to " . urldecode($email_address) . ". Please check you email box")
							: "Message does not send due to internal errors.");
					}
				}
			}
			else if($action == 'proceed') {
				$reg_key = stripslashes(trim($matches['key'][0]));
				$query = <<<SQL
SELECT
	rk.registration_id,
	rk.registration_email
FROM
	registration_keys rk
WHERE
	rk.registration_key = '$reg_key'
SQL;
				$result = db_fetch_array(db_query($query));
				if ($result) {
					if (!isset($values['register_password']) &&
						!isset($values['register_confirm_password']) &&
						!isset($values['register_first_name']) &&
						!isset($values['register_last_name'])) {
						return account_print_message("The incomplete fields were provided.");
					}
					$password = stripslashes(trim($values['register_password']));
					$confirm_password = stripslashes(trim($values['register_confirm_password']));
					$first_name = stripslashes(trim($values['register_first_name']));
					$last_name = stripslashes(trim($values['register_last_name']));
					if ((empty($password) || empty($confirm_password)) || ($password != $confirm_password)) {
						return array(
							"module"	=>	array(
								"security_token"	=>	"$reg_key",
								"email_address"		=>	urldecode($result['registration_email']),
								"error"				=>	"Passwords are not equal!"
							),
							"title"		=>	"Registration process: provide us your details",
							"template"	=>	"template/account_register_details.tpl"
						);
					}
					else if(empty($first_name) || empty($last_name)) {
						return array(
							"module"	=>	array(
								"security_token"	=>	"$reg_key",
								"email_address"		=>	urldecode($result['registration_email']),
								"error"				=>	"First name and Last name are required!"
							),
							"title"		=>	"Registration process: provide us your details",
							"template"	=>	"template/account_register_details.tpl"
						);
					}
					else {
						$password = md5($password);
						$email_address = $result['registration_email'];
						$query = <<<SQL
INSERT INTO users
VALUES(NULL, '$password')
SQL;
						$result = db_query($query);
						if ($result) {
							$user_id = db_last_insert_id($result);
							$query = <<<SQL
INSERT INTO user_address
VALUES(NULL, $user_id, '$email_address', '$first_name', '$last_name')
SQL;
							$result = db_query($query);
							if ($result) {
								$query = <<<SQL
DELETE FROM registration_keys
WHERE registration_key = '$reg_key'
SQL;
								db_query($query);
								return account_print_message("Congratulations! We have completely register your account. You can login now.");
							}
							else {
								$query = <<<SQL
DELETE FROM users
WHERE user_id = $user_id
SQL;
								db_query($query);
								return account_print_message("The problem occured while registering account. Please come back later. Thanks!");
							}
						}
						else {
							return account_print_message("The problem occured while registering account. Please come back later. Thanks!");
						}
					}
				}
				else {
					return account_print_message("The security token doesn't exists.");
				}
			}
		}
	}

	function account_send_mail($subject, $to, $content) {
		$headers = <<<MAIL
From: donotreply@uploadbox.com
X-Mailer: PHP
MAIL;
		return mail($to, $subject, $content, $headers);
	}

	function account_generate_key() {
		return session_secure_id();
	}

	function account_print_message($message) {
		return array(
			"module"	=>	array(
				"message"	=>	$message
			),
			"title"		=>	"Registration process: email validation",
			"template"	=>	"template/account_register_message.tpl"
		);
	}

?>
