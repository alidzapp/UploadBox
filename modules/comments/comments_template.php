<?php

	require_once "includes/template.php";

	function comments_template_processor(&$values = null) {
		global $active_user;
		if (!isset($values)) {
			return '';
		}
		// build comment's tree
		foreach($values as $id => $comment_value) {
			if (isset($comment_value['children'])) {
				comments_template_processor_childs($values[$id]['children'], LEVEL_PADDING);
			}
			$comments_user = array();
			if (isset($comment_value['user_id'])) {
				$comments_user = comments_user_name($comment_value['user_id']);
			}
			$values[$id]["comment_content"] = show_template_wrapper(file_get_contents("template/blank.tpl"),
				array(
					array(
						"module"	=>	array(
							"comment-content"		=>	urldecode($comment_value['comment_content']),
							"comment-file-id"		=>	$comment_value['client_file_id'],
							"comment-file-hash"		=>	$comment_value['file_hash'],
							"comment-id"			=>	$comment_value['comment_id'],
							"comment-user-name"		=>	$comment_value['user_name'],
							"comment-user-id"		=>	$comment_value['user_id'],
							"user-id"				=>	isset($active_user) ? $active_user['user_id'] : 0,
							"comment-date"			=>	$comment_value['comment_date'],
							"comment-parent-id"		=>	$comment_value['parent_comment_id'],
							"comment-owner-details"	=>	isset($comment_value['user_id'])
								? get_html_user_details($comment_value['user_id']) : $comment_value['user_name'],
							"padding-left"			=> 0
						),
						"template"	=>	"template/comment_tree_item.tpl"
					)
				)
			);
		}
		return comments_template_processor_tree_builder($values);
	}

	define("LEVEL_PADDING", 25);

	function comments_template_processor_childs(&$values, $level = 0) {
		global $active_user;
		$result = array();
		foreach($values as $id => $comment_value) {
			if (isset($comment_value['children'])) {
				comments_template_processor_childs($values[$id]['children'], $level + LEVEL_PADDING);
			}
			$comments_user = array();
			if (isset($comment_value['user_id'])) {
				$comments_user = comments_user_name($comment_value['user_id']);
			}
			$values[$id]["comment_content"] = show_template_wrapper(file_get_contents("template/blank.tpl"),
				array(
					array(
						"module"	=>	array(
							"comment-content"		=>	urldecode($comment_value['comment_content']),
							"comment-id"			=>	$comment_value['comment_id'],
							"comment-file-id"		=>	$comment_value['client_file_id'],
							"comment-file-hash"		=>	$comment_value['file_hash'],
							"comment-user-name"		=>	$comment_value['user_name'],
							"comment-user-id"		=>	$comment_value['user_id'],
							"user-id"				=>	isset($active_user) ? $active_user['user_id'] : 0,
							"comment-date"			=>	$comment_value['comment_date'],
							"comment-parent-id"		=>	$comment_value['parent_comment_id'],
							"comment-owner-details"	=>	isset($comment_value['user_id'])
								? get_html_user_details($comment_value['user_id']) : $comment_value['user_name'],
							"padding-left"			=>	$level
						),
						"template"	=>	"template/comment_tree_item.tpl"
					)
				)
			);
		}
		return $result;
	}

	function comments_template_processor_tree_builder($html_values) {
		$output = '';
		foreach($html_values as $id => $value) {
			$output .= $value['comment_content'];
			if (isset($value['children'])) {
				$output .= comments_template_processor_tree_builder($value['children']);
			}
		}
		return $output;
	}
?>
