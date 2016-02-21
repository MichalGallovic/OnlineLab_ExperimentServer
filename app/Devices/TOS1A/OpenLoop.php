<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use App\Events\ProcessWasRan;

class OpenLoop extends AbstractTOS1A implements DeviceDriverContract
{
	public function __construct($device,$experimentType) {
		parent::__construct($device,$experimentType);
		$this->scriptNames["openloop"] = "run.py";
		
		// Definijng input arguments and rules
		// for validation
		$this->rules = [
			"c_fan" => "required",
			"c_lamp" => "required",
			"c_led" => "required",
			"t_sim" => "required"
		];
	}

	public function run($input, $requestedBy) {
		parent::run($input, $requestedBy);
		
		$process =  $this->runExperiment($input);

		$this->experimentStartedRunning = time();
		
		$writingProcess = $this->startReadingExperiment($this->simulationTime);
		
		$this->attachPid($writingProcess->getPid());

		$seconds = 0;
		while($seconds < $this->simulationTime) {
			usleep(1000000);
			$seconds++;
		}

		// We will wait until the process stops (if it started)
		// because it is ran asynchronously
		if(!is_null($writingProcess)) {
			while($writingProcess->isRunning()) {}
		}

		event(new ProcessWasRan($writingProcess,$this->device));

		$this->stop();
	
	}
}