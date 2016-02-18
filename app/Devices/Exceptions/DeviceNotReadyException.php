<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DeviceNotReadyException extends \Exception {

	use ApiRespondable;

	public function getResponse() {
		return $this->errorForbidden("Device not ready");
}