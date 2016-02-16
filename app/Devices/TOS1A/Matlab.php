<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Events\ProcessWasRan;
use Illuminate\Support\Facades\Cache;
use App\Devices\Exceptions\ExperimentTimedOutException;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{

	protected $simulationTime;

	public function __construct($device) {
		parent::__construct($device);
		$this->scriptNames["matlab"] = "matlab/run.py";
		
		// Definijng input arguments and rules
		// for validation
		$this->rules = [
			"P" => "required",
			"I" => "required",
			"D" => "required",
			"c_fan" => "required",
			"c_lamp" => "required",
			"c_led" => "required",
			"in_sw" => "required",
			"out_sw" => "required",
			"t_sim" => "required",
			"s_rate" => "required",
			"input" => "required",
			"scifun" => "required"
		];
	}

	public function run($input) {
		parent::run($input);
		
		$process =  $this->runExperiment($input);
		
		$experimentStarted = false;
		$writingProcess = null;
		$started = time();
		$startedRunningExperiment = 0.00;
		$experimentTimedOut = false;
		// We start the reading script only after the experiment
		// starts running - that is after matlab initializes
		// itself
		// We figure that out by asking every sec
		// if we get any different output than
		// 0.00 from device
		while($process->isRunning()) {
			// check some stuff with timeout
			$now = time();
			
			// if($now - $started > 5) break;

			if(!$experimentStarted) {
				if($this->isExperimenting()) {
					$experimentStarted = true;
					$startedRunningExperiment = time();
					$writingProcess = $this->startReadingExperiment($this->simulationTime);
					$this->attachPid($writingProcess->getPid());
				}
			} else {
				if($now - $startedRunningExperiment > $this->simulationTime + 10) {
					$experimentTimedOut = true;
					break;
				}
			}

			usleep(1000000);

		}

		// We will wait until the process stops (if it started)
		// because it is ran asynchronously
		if(!is_null($writingProcess)) {
			while($writingProcess->isRunning()) {}
		}

		event(new ProcessWasRan($process,$this->device));
		event(new ProcessWasRan($writingProcess,$this->device));

		$this->stop();

		if($experimentTimedOut) {
			throw new ExperimentTimedOutException;
		}
		
		return $this->read();
	}

	protected function runExperiment($arguments) {
		$path = $this->getScriptPath("matlab");

		// matlab starts 15s this should be automated
		$this->simulationTime = $arguments["t_sim"];
		$timeout = $this->simulationTime + 20;
		$arguments = $this->prepareArguments($arguments);
		$process = $this->runProcessAsync($path, $arguments, $timeout);
		$this->attachPid($process->getPid());
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