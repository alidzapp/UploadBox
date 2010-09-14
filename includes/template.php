<?php

	require_once "hooks.php";

	function show_template($path, $template_vars = array()) {
		$main_template_content = file_get_contents($path);
		$hooks = search_for_hooks($main_template_content);
		if (!empty($hooks)) {
			$main_template_content = process_hooks($main_template_content, $path, $hooks);
		}
		$title = '';
		foreach($template_vars as $module_name => $template_details) {
			if (empty($title) && isset($template_details['title'])) {
				$title = $template_details['title'];
			}
			if (!isset($template_details['template'])) {
				continue;
			}
			if (isset($template_details['template_override']) && is_array($template_details['template_override'])) {
				$template_override = process_template_override($path, $template_details['template_override']);
				$template_vars[$module_name]['module'][$template_details['template_override']['key']] = $template_override;
				unset($template_vars[$module_name]['template_override']);
			}
		}
		$main_template_content = str_replace("#title#", $title, $main_template_content);
		echo show_template_wrapper($main_template_content, $template_vars, "#content#", $title);
	}

	function show_template_wrapper($main_template_content, $template_vars = array(), $content_key = '#content#', $title = '') {
		$content = '';
		foreach($template_vars as $module_name => $template_details) {
			if (!isset($template_details['template'])) {
				continue;
			}
			$template_use = $template_details['template'];
			$sub_module_content = '';
			if (file_exists($template_use)) {
				$sub_module_content = file_get_contents($template_use);
			}
			if (empty($template_details['module'])) {
				$content .= $sub_module_content;
			}
			else {
				$content .= $sub_module_content;
				foreach($template_details['module'] as $template_key => $template_value) {
					if (is_array($template_value)) {
						$array_key_value = process_array_vars($template_key, $template_value, $content);
						$content = preg_replace("/<array\s+\@$template_key\@[^>]*>(.*?)<\/array>/sm", $array_key_value, $content);
					}
					else {
						$content = process_expr_content($content, "#" . $template_key . "#", $template_value);
						$content = preg_replace("/#$template_key#/i", $template_value, $content);
					}
				}
			}
		}
		$main_template_content = str_replace($content_key, $content, $main_template_content);
		return $main_template_content;
	}

	function process_array_vars($template_key, $template_value, $content) {
		$matches = array();
		$matched = '';
		$num_matches = preg_match("/<array\s+\@$template_key\@[^>]*>(.*?)<\/array>/sm", $content, $matches);
		if ($num_matches) {
			$matched = $matches[1];
			$header_content = process_array_header($matched);
			$footer_content = process_array_footer($matched);

			if (empty($template_value)) {
				$matched = preg_replace("/<array:header[^>]*>(.*?)<\/array:header>/sm", $header_content, $matched);
				$matched = preg_replace("/<array:footer[^>]*>(.*?)<\/array:footer>/sm", $footer_content, $matched);
				$matched = preg_replace("/<array:body[^>]*>(.*?)<\/array:body>/sm", '', $matched);
				$matched = preg_replace("/<array:empty[^>]*>(.*?)<\/array:empty>/sm", '', $matched);
			}
			else {
				$matched = preg_replace("/<array:empty[^>]*>(.*?)<\/array:empty>/sm", '', $matched);
				$matched = preg_replace("/<array:header[^>]*>(.*?)<\/array:header>/sm", $header_content, $matched);
				$matched = preg_replace("/<array:footer[^>]*>(.*?)<\/array:footer>/sm", $footer_content, $matched);
				$body_content_num = preg_match("/<array:body[^>]*>(.*?)<\/array:body>/sm", $content, $matches);
				$body_content = '';
				if ($body_content_num) {
					$body_content = $matches[1];
				}
				$processed_body_content = '';
				foreach ($template_value as $array_item) {
					$value = $body_content;
					foreach ($array_item as $array_key => $array_value) {
						$value = process_expr_content($value, "\@" . $array_key . "\@", $array_value);
						$value = preg_replace("/<array:value\s+\@$array_key\@\s?\/>/sm", $array_value, $value);
					}
					$processed_body_content .= $value;
				}
				$matched = preg_replace("/<array:body[^>]?>(.*?)<\/array:body>/sm", $processed_body_content, $matched);
			}
		}
		return $matched;
	}

	function process_array_header($content) {
		$matches = array();
		$num_matches = preg_match("/<array:header[^>]*>(.*?)<\/array:header>/sm", $content, $matches);
		if ($num_matches) {
			return $matches[1];
		}
		return '';
	}

	function process_expr_content($content, $template_key, $template_value) {
		$matches = array();
		$pattern = "/<if\s*(?P<expr>notempty|empty|zero|notzero)=" . $template_key . "[^>]*>(.*?)<\/if>/sm";
		$matches_count = preg_match_all($pattern, $content, $matches);
		if ($matches_count) {
			foreach ($matches[0] as $matched_idx => $matched_value) {
				$expr = $matches['expr'][$matched_idx];
				if (($expr == 'notempty' && $template_value != '') ||
					($expr == 'empty' && $template_value == '') || 
					($expr == 'zero' && $template_value == 0) ||
					($expr == 'notzero' && $template_value != 0)) {
					$item_value = substr($matches[2][$matched_idx], 0);
					$item_value = preg_replace("/$template_key/i", $template_value, $item_value);
					$content = str_replace($matched_value, $item_value, $content);
				}
				else {
					$content = str_replace($matched_value, '', $content);
				}
			}
		}
		return $content;
	}

	function process_array_footer($content) {
		$matches = array();
		$num_matches = preg_match("/<array:footer[^>]*>(.*?)<\/array:footer>/sm", $content, $matches);
		if ($num_matches) {
			return $matches[1];
		}
		return '';
	}

	function process_template_override($path, $module_vars) {
		$result = '';
		$template_processor = $module_vars['processor'];
		if (file_exists($template_processor)) {
			require_once $template_processor;
			if (isset($module_vars['callback']) && function_exists($module_vars['callback'])) {
				$result = call_user_func_array($module_vars['callback'], $module_vars['values']);
			}
		}
		return $result;
	}

	function search_for_hooks($content) {
		$result = array();
		$matches = array();
		$matches_count = preg_match_all("/\@hook:(.+)?[^\@]?\@/i", $content, $matches);
		if ($matches_count) {
			$result = $matches[1];
		}
		return $result;
	}

?>
