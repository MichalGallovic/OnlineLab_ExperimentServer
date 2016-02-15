<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Validator;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Events\ProcessWasRan;
use Illuminate\Support\Facades\Cache;


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
					$writingProcess = $this->startReadingExperiment();
					$this->attachPid($writingProcess->getPid());
				}
			} else {
				if($now - $startedRunningExperiment > $this->simulationTime + 5) break;
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

	protected function startReadingExperiment() {
		$path = $this->getScriptPath("readexperiment");
		$arguments = [
			$this->device->port,
			$this->device->uuid,
			$this->simulationTime,
			200
		];

		$process = $this->runProcessAsync($path, $arguments);

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

	protected function validateInput($input) {
		if(!is_array($input)) {
			throw new ParametersInvalidException("Experiment Arguments");
		}

		$validator = Validator::make($input, $this->rules);

		if($validator->fails()) {
			throw new ParametersInvalidException($validator->messages());
		}
	}

	public function stop() {
		// Stops matlab and cleans up all processes
		if(!is_null($this->device->attached_pids)) {
			$this->stopExperimentRunner();
		}
		// Stop the experiment on the physical device
		$this->stopDevice();
		// Detaches the main process pid from db
		$this->detachPids();
	}

	protected function attachPid($pid) {
		$this->device->fresh();
		$pids = json_decode($this->device->attached_pids);
		$pids []= $pid;
		$this->device->attached_pids = json_encode($pids);
		$this->device->save();
	}

	protected function detachPids() {
		$this->device->attached_pids = null;
		$this->device->save();
	}

	protected function stopExperimentRunner() {
		$this->device->fresh();
		$attached_pids = json_decode($this->device->attached_pids);
		$pids = [];
		foreach ($attached_pids as $pid) {
			$pids = array_merge($this->getAllChildProcesses($pid), $pids);
		}

		// Kill all processes created for experiment running
		foreach ($pids as $pid) {
			$arguments = [
				"-TERM",
				$pid
			];
			$process = $this->runProcessWithoutLog("kill",$arguments);
		}
	}

	/**
	 * Method uses pstree to get a tree of all
	 * subprocesses created by a process
	 * defined with PID
	 *
	 * It returns array with all processes created
	 * for python+experiment runner and also
	 * contains the pid of parent process
	 * @return array
	 */
	protected function getAllChildProcesses($pid) {
		$process = new Process("pstree -p ". $pid ." | grep -o '([0-9]\+)' | grep -o '[0-9]\+'");
		 
		$process->run();
		$allProcesses = array_filter(explode("\n",$process->getOutput()));

		return $allProcesses;
	}
}