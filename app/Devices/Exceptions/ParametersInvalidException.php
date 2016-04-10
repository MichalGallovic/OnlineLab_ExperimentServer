<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class ParametersInvalidException extends \Exception
{
	use ApiRespondable;

	protected $messages;

	public function __construct($messages) {
		$this->messages = $messages;
	}

	public function getResponse() {
		return $this->errorWrongArgs($this->messages);
	}
}