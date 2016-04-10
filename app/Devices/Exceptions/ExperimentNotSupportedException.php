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
	protected $softwareName;

	public function __construct($softwareName)
	{
		$this->softwareName = $softwareName;
	}

	public function getResponse() {
		$message = Str::ucfirst($this->softwareName) . " implementation is not supported on this device";
		return $this->errorForbidden($message);
	}
}