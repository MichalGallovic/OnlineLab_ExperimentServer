<?php

namespace App\Classes\Validators;

/**
* Experiment Service Validator
*/
class ExperimentServiceValidator
{
	protected $deviceName;
	protected $softwareName;

	public function __construct($deviceName, $softwareName)
	{
		$this->deviceName = $deviceName;
		$this->softwareName = $softwareName;
	}

	public function validate()
	{
		$commands = $this->getExperimentCommands($deviceName, $softwareName);
		if(is_null($commands) || !is_array($commands) || empty($commands)) {
			throw new ExperimentCommandsNotDefined($deviceName, $softwareName);
		}
	}
	
}