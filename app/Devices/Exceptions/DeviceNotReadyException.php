<?php namespace App\Devices\Exceptions;

class DeviceNotReadyException extends \Exception {

	public function getResponse() {
		return response()->json([
				"error" => "Device not ready"
			],400);
	}
}