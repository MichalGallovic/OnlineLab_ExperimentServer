<?php

namespace App\Classes\Services\Exceptions;

class ExperimentCommandsNotDefined extends \Exception
{
	
	public function __construct($deviceName, $softwareName)
	{
		$this->message = "There are no defined commands for experiment: " . $deviceName . " : " . $softwareName;
	}
}