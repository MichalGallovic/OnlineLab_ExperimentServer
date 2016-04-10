<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DeviceAlreadyRunningExperimentException extends \Exception {

	use ApiRespondable;

	public function getResponse() {
		return $this->errorForbidden("Device is already running experiment");
	}
}