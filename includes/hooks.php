<?php

	require_once "db.php";
	require_once "template.php";

	function process_hooks($content, $path, $hooks = array()) {
		$query = <<<SQL
SELECT
	m.*
FROM
	modules m
WHERE
	m.module_available = 1
ORDER BY
	m.module_order ASC
SQL;
		$result = db_fetch_arrays(db_query($query));
		if (!empty($result)) {
			$params = array();
			foreach($hooks as $hook) {
				foreach($result as $module_details) {
					if (file_exists($module_details['module_path'])) {
						require_once $module_details['module_path'];
						$hook_func = $module_details['module_name'] . "_hook";
						if (function_exists($hook_func)) {
							$func_params = array(
								"type"				=>	$hook,
								"path"				=>	$path,
								"module_details"	=>	$module_details,
								"params"			=>	($_SERVER['REQUEST_METHOD'] == 'POST')
									? $_POST : $_GET
							);
							array_push($params, call_user_func_array($hook_func, $func_params));
						}
					}
				}
				$content = show_template_wrapper($content, $params, "@hook:$hook@");
			}
		}
		return $content;
	}

?>
