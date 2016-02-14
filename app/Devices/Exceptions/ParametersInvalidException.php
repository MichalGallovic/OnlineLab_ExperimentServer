<?php namespace App\Devices\Exceptions;

class ParametersInvalidException extends \Exception
{
	protected $messages;

	public function __construct($messages) {
		$this->messages = $messages;
	}

	public function getResponse() {
		return response()->json([
				"error" => $this->messages
			],400);
	}
}