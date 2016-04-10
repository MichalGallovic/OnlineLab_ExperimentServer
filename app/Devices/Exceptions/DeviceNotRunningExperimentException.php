<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DeviceNotRunningExperimentException extends \Exception {

	use ApiRespondable;

	public function getResponse() {
		return $this->errorForbidden("Device is not running experiment");
	}
}