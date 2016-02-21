<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;
use Illuminate\Support\Str;

class ExperimentNotSupportedException extends \Exception {

	use ApiRespondable;

	/**
	 * Not supported experiment type on device
	 * @var string
	 */
	protected $experimentType;

	public function __construct($experimentType)
	{
		$this->experimentType = $experimentType;
	}

	public function getResponse() {
		$message = Str::ucfirst($this->experimentType) . " expriment is not supported on this device";
		return $this->errorForbidden($message);
	}
}