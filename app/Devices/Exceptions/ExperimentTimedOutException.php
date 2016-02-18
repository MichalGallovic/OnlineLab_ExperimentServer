<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class ExperimentTimedOutException extends \Exception {

	use ApiRespondable;

	public function getResponse() {
		return $this->errorForbidden("Experiment timed out (took longer than expected)");
	}
}