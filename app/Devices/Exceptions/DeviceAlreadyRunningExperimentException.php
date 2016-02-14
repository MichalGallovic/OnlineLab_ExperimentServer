<?php namespace App\Devices\Exceptions;

class DeviceAlreadyRunningExperimentException extends \Exception {

	public function getResponse() {
		return response()->json([
				"error" => "Device is already running experiment"
			],400);
	}
}