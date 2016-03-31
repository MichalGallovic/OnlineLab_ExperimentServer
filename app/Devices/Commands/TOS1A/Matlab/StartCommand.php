<?php

namespace App\Devices\Commands\TOS1A\Matlab;

use App\Experiment;
use App\Devices\Helpers\Logger;
use App\Devices\Scripts\Script;
use App\Devices\Commands\StartCommand as GeneralStartCommand;

/**
* Strat Command TOS1A/Matlab
*/
class StartCommand extends GeneralStartCommand
{
	
	public function __construct(Experiment $experiment, Script $startScript, Script $stopScript, Logger $logger)
	{
		parent::__construct($experiment, $startScript, $stopScript, $logger);
		$input = $this->startScript->getInput();
		$this->setMeasuringRate($input);
		$this->setSimulationTime($input);
	}

	protected function getMeasuringRate($input)
	{
		return $input["s_rate"];
	}

	protected function getSimulationTime($input)
	{
		return $input["t_sim"];
	}
}