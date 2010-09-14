<?php

	/**
	 *
	 */
	function turnon_buffer() {
		if (!headers_sent()) {
			ob_start();
		}
	}

	/**
	 *
	 */
	function flush_buffers() {
		if (!headers_sent()) {
			ob_end_flush();
		}
	}

	/**
	 *
	 */
	function cleanup_buffers($end = false) {
		if (!headers_sent()) {
			if ($end) {
				ob_end_clean();
			}
			else {
				ob_clean();
			}
		}
	}
?>
