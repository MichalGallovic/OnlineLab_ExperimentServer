<?php

if(!function_exists('devices_path')) {
	function devices_path($path = "") {
		if(!empty($path)) {
			$path = $path[0] == DIRECTORY_SEPARATOR ? $path : DIRECTORY_SEPARATOR.$path;
		}

		return app_path("Devices" . $path);
	}
}