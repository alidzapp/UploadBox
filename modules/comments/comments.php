<?php
/*
	function comments_boot($path, $module_details, $values) {
	}
*/
	function comments_deps($path, $module_details, $values = array()) {
		global $delete_file_hooks;
		if (!isset($delete_file_hooks)) {
			$delete_file_hooks = array();
		}
		array_push($delete_file_hooks, "delete_comments");
		global $active_user;
		preg_match('/^\/form\/(?P<action>get|download)\/(.*)$/', $path, $matches);
		if (!empty($matches)) {
			$action_taken = $matches['action'];
			if ($action_taken == 'get') {
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
LEFT JOIN
	client_grant_comments cgc
		ON cgc.client_file_id = cf.client_file_id
WHERE
	cf.file_hash = '$file_hash'
AND
	cgc.comment_grant_id IS NOT NULL
SQL;
					$result = db_fetch_array(db_query($query));
					if ($result) {
						$comments = collect_comments_for_file($result['client_file_id'], 0);
						if ($active_user) {
							return array(
								"module"	=>	array(
									"file_id"	=>	$result['client_file_id'],
									"file_hash"	=>	$result['file_hash'],
									"parent_id"	=>	0
								),
								"title"		=>	"",
								"template"	=>	"template/comment_form_user.tpl",
								"template_override"	=>	array(
									"key"			=>	"comments-content",
									"processor"		=>	"modules/comments/comments_template.php",
									"callback"		=>	"comments_template_processor",
									"values"		=>	$comments
								)
							);
						}
						else {
							return array(
								"module"	=>	array(
									"file_id"	=>	$result['client_file_id'],
									"parent_id"	=>	0,
									"file_hash"	=>	$result['file_hash']
								),
								"title"		=>	"",
								"template"	=>	"template/comment_form.tpl",
								"template_override"	=>	array(
									"key"			=>	"comments-content",
									"processor"		=>	"modules/comments/comments_template.php",
									"callback"		=>	"comments_template_processor",
									"values"		=>	$comments
								)
							);
						}
					}
				}
			}
		}
	}
/*
	function comments_get($path, $module_details, $values = array()) {
	}
*/
	function comments_post($path, $module_details, $values = array()) {
		global $active_user;
		$matches_count = preg_match_all("/\/form\/comment\/(?<action>allow|deny)\//i", $path, $matches);
		if ($matches_count) {
			$action_taken = $matches['action'][0];
			$file_hash = isset($values['file_hash']) ? urldecode(stripslashes($values['file_hash'])) : '';
			$file_id = 0;
			if (!empty($file_hash)) {
				$query = <<<SQL
SELECT
	cf.client_file_id
FROM
	client_files cf
WHERE
	cf.file_hash = '$file_hash'
SQL;
				$file_details = db_fetch_array(db_query($query));
				if(isset($file_details)) {
					$query = <<<SQL
SELECT
	cgc.*
FROM
	client_grant_comments cgc
WHERE
	cgc.client_file_id = $file_id
SQL;
					$grant_comments = db_fetch_array(db_query($query));
					if(!$grant_comments) {
						$file_id = $file_details['client_file_id'];
					}
				}
			}
			if ($action_taken == 'allow') {
				if ($file_id) {
					$query = <<<SQL
INSERT INTO client_grant_comments
VALUES(NULL, $file_id)
SQL;
					db_query($query);
				}
			}
			redirect_to_url($values['back_url']);
			exit();
		}
		if (isset($values['comment_file_id']) && isset($values['comment_value'])) {
			$comment_value = urlencode(stripslashes(trim($values['comment_value'])));
			$comment_parent_id = urlencode(stripslashes(trim($values['comment_parent_id'])));
			$comment_name = isset($values['comment_name']) ? urlencode(stripslashes(trim($values['comment_name']))) : '';
			$comment_file_id = urlencode(stripslashes(trim($values['comment_file_id'])));
			if (isset($active_user) && isset($comment_file_id)) {
				$user_id = $active_user['user_id'];
				$query = <<<SQL
INSERT INTO comments
VALUES(NULL, '$comment_value', NOW(), $comment_file_id, $user_id, NULL, $comment_parent_id)
SQL;
			}
			else if(isset($comment_name)) {
				$query = <<<SQL
INSERT INTO comments
VALUES(NULL, '$comment_value', NOW(), $comment_file_id, NULL, '$comment_name', $comment_parent_id)
SQL;
			}
			$result = db_query($query);
			//if ($result) {
			$query = <<<SQL
SELECT
	cf.file_hash
FROM
	client_files cf
WHERE
	cf.client_file_id = $comment_file_id
SQL;
			$res = db_fetch_array(db_query($query));
			redirect_to_url($values['back_url']);
			//}
		}
	}

	function collect_comments_for_file($file_id, $parent_comment_id, $comments = array()) {
		$query = <<<SQL
SELECT
	c.comment_id,
	c.comment_content,
	c.comment_date,
	c.client_file_id,
	cf.file_hash,
	c.user_id,
	c.user_name,
	c.parent_comment_id
FROM comments c
JOIN client_files cf
	ON c.client_file_id = cf.client_file_id
WHERE
	c.parent_comment_id = $parent_comment_id
AND
	c.client_file_id = $file_id
SQL;
		$result = db_fetch_arrays(db_query($query));
		if (!empty($result)) {
			$comments['children'] = $result;
			foreach($result as $id => $comment_value) {
				collect_comments_for_file($file_id,
					$comment_value['comment_id'], &$comments['children'][$id]);
			}
		}
		return $comments;
	}

	function delete_comments($file_id) {
		$query = <<<SQL
DELETE FROM comments
WHERE client_file_id = $file_id
SQL;
		db_query($query);
		$query = <<<SQL
DELETE FROM client_grant_comments
WHERE  client_file_id = $file_id
SQL;
		db_query($query);
	}

	function comments_user_name($user_id) {
		$query = <<<SQL
SELECT
	ua.user_email_address,
	ua.user_first_name,
	ua.user_last_name,
	u.user_id
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

?>
