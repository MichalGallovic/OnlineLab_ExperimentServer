<?php namespace App\Devices\Exceptions;

class DeviceNotConnectedException extends \Exception {

	public function getResponse() {
		return response()->json([
				"error" => "Device not connected"
			],400);
	}
}