<?php

	require_once "config.php";
	require_once "common.php";
	require_once "db.php";
	require_once "url.php";
	require_once "template.php";


	function bootstrap() {
		turnon_buffer();
		db_set_active();
		url_process();
		flush_buffers();
	}

	/**
	 * Bootstrap a module associated with URL.
	 */
	function module_bootstrap($path, $module_details) {
		if (file_exists($module_details['module_path'])) {
			$template_vars = array();
			$result = boot_module($path, $module_details);
			if ($result) {
				foreach($result as $module_name => $module_template_vars) {
					$template_vars[$module_name] = $module_template_vars;
				}
			}
			$result = check_and_boot_dependencies($path, $module_details);
			if ($result) {
				foreach($result as $module_name => $module_template_vars) {
					$template_vars[$module_name] = $module_template_vars;
				}
			}
			$result = load_module($path, $module_details);
			if ($result) {
				foreach($result as $module_name => $module_template_vars) {
					$template_vars[$module_name] = $module_template_vars;
				}
			}
			uasort($template_vars, "compare_module_order");
			load_main_template($template_vars);
		} else {
			module_not_presented_error($module_details);
		}
	}


	/**
	 * Decide module order execution.
	 */
	function compare_module_order($a, $b) {
		if ($a['order'] == $b['order']) {
			return 0;
		}
		return ($a['order'] < $b['order'] ? -1 : 1);
	}

	/**
	 * Just print an error if module not exists at file system as file.
	 */
	function module_not_presented_error($module_details) {
		cleanup_buffers(true);
		header ('HTTP/1.1 503 Service Unavailable');
		print "<pre>";
		print "System module not found: <b>" . $module_details['module_name'] . "</b><br />";
		print "<b><u>Check the path</u></b>: " . $module_details['module_path'] . ".<br />";
		print "</pre>";
		exit;
	}

	/**
	 * Check for module dependencies and boot it if necessary.
	 */
	function check_and_boot_dependencies($path, $module_details, $deps_caller_suffix = '_deps') {
		global $active_db;
		$mid = $module_details['module_id'];
		$query = <<<SQL
SELECT
	m.module_depends_on,
	m.module_order
FROM
	modules m
WHERE
	m.module_id = $mid
SQL;
		$result = db_fetch_array(db_query($query));
		if ($result) {
			if (empty($result['module_depends_on'])) {
				return array();
			}
			$template_vars = array();
			$modules = explode(',', $result['module_depends_on']);
			$modules_pre_order = array();
			foreach($modules as $depend_module) {
				$query = <<<SQL
SELECT
	m.module_id,
	m.module_path,
	m.module_name,
	m.module_description,
	m.module_order
FROM
	modules m
WHERE
	m.module_id = $depend_module
AND
	m.module_available = 1
SQL;
				$dep_module_details = db_fetch_array(db_query($query));
				if ($dep_module_details) {
					if (!isset($modules_pre_order[$dep_module_details['module_order']])) {
						$modules_pre_order[$dep_module_details['module_order']] = array();
						$modules_pre_order[$dep_module_details['module_order']]['order'] = $dep_module_details['module_order'];
					}
					array_push(
						$modules_pre_order[$dep_module_details['module_order']], $dep_module_details
					);
				}
			}
			uasort($modules_pre_order, "compare_module_order");
			foreach ($modules_pre_order as $key => $order) {
				unset($modules_pre_order[$key]['order']);
			}
			$modules_order = array();
			foreach ($modules_pre_order as $order) {
				foreach ($order as $module_details) {
					array_push(
						$modules_order,
						$module_details
					);
				}
			}
			foreach ($modules_order as $dep_module_details) {
				require_once $dep_module_details['module_path'];
				$result = array();
				$deps_func = $dep_module_details['module_name'] . $deps_caller_suffix;
				$params = array(
					"path"				=>	$path,
					"module_details"	=>	$dep_module_details,
					"params"			=>	($_SERVER['REQUEST_METHOD'] == 'POST')
					? $_POST
					: $_GET
				);
				if (function_exists ($deps_func)) {
					$result = call_user_func_array ($deps_func, $params);
					$template_vars[$dep_module_details['module_name']] = $result;
					$template_vars[$dep_module_details['module_name']]['order'] = $dep_module_details['module_order'];
				}
			}
			return $template_vars;
		}
		return array();
	}

	/**
	 * Load a module.
	 */
	function load_module($path, $module_details) {
		$template_vars = array();
		$params = array(
			"path"				=>	$path,
			"module_details"	=>	$module_details
		);
		require_once $module_details['module_path'];
		$call_func = $module_details['module_name'] . "_get";
		$params["params"] = ($_SERVER['REQUEST_METHOD'] == 'POST')
			? $_POST
			: $_GET;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$call_func = $module_details['module_name'] . "_post";
		}
		if (function_exists ($call_func)) {
			$template_vars[$module_details['module_name']]['order'] = $module_details['module_order'];
			$result = call_user_func_array (
				$call_func,
				$params
			);
			if ($result) {
				foreach ($result as $template_key => $template_value) {
					$template_vars[$module_details['module_name']][$template_key] = $template_value;
				}
			}
		}
		return $template_vars;
	}

	/**
	 * Boot a module.
	 */
	function boot_module($path, $module_details) {
		$template_vars = array();
		$params = array(
			"path"				=>	$path,
			"module_details"	=>	$module_details
		);
		require_once $module_details['module_path'];
		$boot_func = $module_details['module_name'] . "_boot";
		$params["params"] = ($_SERVER['REQUEST_METHOD'] == 'POST')
			? $_POST
			: $_GET;
		if (function_exists ($boot_func)) {
			$template_vars[$module_details['module_name']] = call_user_func_array ($boot_func, $params);
			$template_vars[$module_details['module_name']]['order'] = $module_details['module_order'];
		}
		return $template_vars;
	}

	/**
	 * Load main template.
	 */
	function load_main_template($template_vars = array()) {
		show_template('template/index.tpl',
			$template_vars
		);
	}
?>
