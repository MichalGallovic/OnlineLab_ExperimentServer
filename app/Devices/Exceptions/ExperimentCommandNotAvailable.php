<?php

namespace App\Devices\Exceptions;

use App\Experiment;
use App\Classes\Traits\ApiRespondable;

/**
* Exception - Experiment command not available
* Raised when requesting experiment command
* that is not implemented
*/
class ExperimentCommandNotAvailable extends \Exception
{
	use ApiRespondable;

	protected $experiment;

	protected $commandType;

	public function __construct(Experiment $experiment, $commandType)
	{
		$this->experiment = $experiment;
		$this->commandType = $commandType;
	}

	public function getResponse() {
		return $this->errorForbidden($this->commandType . " command is not available on " . $this->experiment->device->type->name . " software " . $this->experiment->software->name . " implementation");
	}
}