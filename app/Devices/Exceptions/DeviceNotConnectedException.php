<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DeviceNotConnectedException extends \Exception {

	use ApiRespondable;

	public function getResponse() {
		return $this->errorInternalError("Device not connected");
	}
}