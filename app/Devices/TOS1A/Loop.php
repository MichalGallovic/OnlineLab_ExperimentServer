<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use App\Events\ProcessWasRan;

class Loop extends AbstractTOS1A implements DeviceDriverContract
{
	public function __construct($device) {
		parent::__construct($device);
		$this->scriptNames["loop"] = "run.py";
		
		// Definijng input arguments and rules
		// for validation
		$this->rules = [
			"c_fan" => "required",
			"c_lamp" => "required",
			"c_led" => "required",
			"t_sim" => "required"
		];
	}

	public function run($input) {
		parent::run($input);
		
		$process =  $this->runExperiment($input);
		
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
		
		return $this->read();
	}

	protected function runExperiment($arguments) {
		$path = $this->getScriptPath("loop");

		// matlab starts 15s this should be automated
		$this->simulationTime = $arguments["t_sim"];
		$timeout = $this->simulationTime + 20;
		$arguments = $this->prepareArguments($arguments);
		$process = $this->runProcess($path, $arguments, $timeout);
		return $process;
	}

	protected function prepareArguments($arguments) {
		$input = "";

		foreach ($arguments as $key => $value) {
			$input .= $key . ":" . $value . ",";
		}
		$input = substr($input, 0, strlen($input) - 1);

		return [
			"--port=" . $this->device->port,
			"--input=" . $input
		];
	}
}