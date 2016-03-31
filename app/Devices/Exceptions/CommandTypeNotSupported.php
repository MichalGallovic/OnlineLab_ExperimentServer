<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;
use Illuminate\Support\Str;

class CommandTypeNotSupported extends \Exception {

	use ApiRespondable;

	/**
	 * Not supported experiment type on device
	 * @var string
	 */
	protected $commandType;

	public function __construct($commandType)
	{
		$this->commandType = $commandType;
	}

	public function getResponse() {
		$message = Str::ucfirst($this->commandType) . " command is not supported in the system.";
		return $this->errorForbidden($message);
	}
}