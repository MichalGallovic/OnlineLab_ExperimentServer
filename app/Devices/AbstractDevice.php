<?php 

namespace App\Devices;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use App\Events\ProcessWasRan;
use Illuminate\Support\Facades\Validator;
use App\Events\ExperimentStarted;
use App\Events\ExperimentFinished;
use Carbon\Carbon;
use App\Devices\Exceptions\DeviceNotRunningExperimentException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;

abstract class AbstractDevice {

	protected $device;
	protected $experimentType;
	protected $experimentStartedRunning;
	protected $experimentSuccessful;

	protected $path;

	const OFFLINE = "offline";
	const READY = "ready";
	const EXPERIMENTING = "experimenting";

	public function __construct($device, $experimentType) {
		$this->device = $device;
		$this->experimentType = $experimentType;
		$this->experimentSuccessful = false;
	}

	public function run($input, $requestedBy) {
		// We don't want to run multiple experiments
		// at the same time, on once device
		if($this->isRunningExperiment()) {
			throw new DeviceAlreadyRunningExperimentException;
		}

		// Validate the input
		$this->validateInput($input);

		// Returning from event listener
		// the first registered is
		// returning logger
		event(new ExperimentStarted($this->device, $this->experimentType, $input, $requestedBy));
	}

	public function stop() {
		// Stops experiment and cleans up all processes
		if(!is_null($this->device->attached_pids)) {
			$this->stopExperimentRunner();
		}
		// Stop the experiment on the physical device
		$this->stopDevice();
		// Detaches the main process pid from db
		$this->detachPids();

		if($this->isLoggingExperiment() && !$this->wasForceStopped()) {
			event(new ExperimentFinished($this->device->currentExperimentLogger));
			$this->experimentSuccessful = true;
		}

		$this->device->detachCurrentExperiment();
	}

	public function forceStop() {
		if(is_null($this->device->currentExperimentLogger)) {
			throw new DeviceNotRunningExperimentException;
		}

		if($this->isLoggingExperiment()) {
			$logger = $this->device->currentExperimentLogger;
			$logger->stopped_at = Carbon::now();
			$logger->save();
		}
		
		// Stops experiment and cleans up all processes
		if(!is_null($this->device->attached_pids)) {
			$this->stopExperimentRunner();
		}
		// Stop the experiment on the physical device
		$this->stopDevice();
		// Detaches the main process pid from db
		$this->detachPids();

		$this->device->detachCurrentExperiment();
	}

	public function experimentWasSuccessful() {
		return $this->experimentSuccessful;
	}

	public function wasForceStopped() {
		if(is_null($this->device->currentExperimentLogger)) {
			return true;
		}
		
		return !is_null($this->device->currentExperimentLogger->stopped_at);
	}

	public function stopDevice() {
		$path = $this->getScriptPath("stop");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
	}

	public function getExperimentDuration() {
		return is_null($this->experimentStartedRunning) ? 0 : time() - $this->experimentStartedRunning;
	}

	protected function validateInput($input) {
		if(!is_array($input)) {
			$arguments = array_keys($this->rules);
			$arguments = implode(" ,", $arguments);
			throw new ParametersInvalidException("Wrong input arguments, expected: [" . $arguments . "]");
		}

		$validator = Validator::make($input, $this->rules);

		if($validator->fails()) {
			throw new ParametersInvalidException($validator->messages());
		}
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

	protected function isLoggingExperiment() {
		$this->device = $this->device->fresh();
		return !is_null($this->device->currentExperimentLogger);
	}

	protected function stopExperimentRunner() {
		$this->device = $this->device->fresh();
		$attached_pids = json_decode($this->device->attached_pids);
		$pids = [];
		foreach ((array)$attached_pids as $pid) {
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

	protected function getScriptPath($name) {
		return $this->scriptsPath . "/" . $this->scriptNames[$name];
	}

	protected function runProcess($path, $arguments = []) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->run();

		event(new ProcessWasRan($process,$this->device));

		return $process;
	}

	// This method is temporary and only called where
	// some error occur in calling processes
	// i.e. killing children processes
	// when forcing experiment to 
	// stop
	// Such occasion producesses lots of errors
	// but works :) - have to fix it
	protected function runProcessWithoutLog($path, $arguments = []) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->run();

		return $process;
	}

	protected function runProcessAsync($path, $arguments = [], $timeout = 20) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->setTimeout($timeout);
		$process->start();

		return $process;
	}

	protected function runExperiment($arguments) {
		$timeout = $this->prepareExperiment($arguments);
		$arguments = $this->prepareArguments($arguments);
		$process = $this->runProcess($this->path, $arguments, $timeout);
		return $process;
	}

	protected function runExperimentAsync($arguments) {
		$timeout = $this->prepareExperiment($arguments);
		$arguments = $this->prepareArguments($arguments);
		$process = $this->runProcessAsync($this->path, $arguments, $timeout);
		$this->attachPid($process->getPid());
		return $process;
	}

	protected function prepareExperiment($arguments) {
		$this->path = $this->getScriptPath($this->experimentType->name);

		// matlab starts 15s this should be automated
		$this->simulationTime = $arguments["t_sim"];
		$timeout = $this->simulationTime + 20;
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

	// protected function runProcessForceAsync($path, $arguments = []) {
	// 	// $builder = new ProcessBuilder();
	// 	// $builder->setPrefix($path);

	// 	// // $arguments []= "> /dev/null";
	// 	// // $arguments []= "2> /dev/null";
	// 	// // $arguments []= "&";

	// 	// $builder->setArguments($arguments);
		
	// 	// $process = $builder->getProcess();
	// 	$process = new Process($path . " > /dev/null 2> /dev/null &");
	// 	$process->run();

	// 	return $process;
	// }
}