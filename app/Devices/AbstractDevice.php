<?php namespace App\Devices;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use App\Events\ProcessWasRan;
use Illuminate\Support\Facades\Validator;

abstract class AbstractDevice {

	protected $device;
	protected $experimentType;

	const OFFLINE = "offline";
	const READY = "ready";
	const EXPERIMENTING = "experimenting";

	public function __construct($device, $experimentType) {
		$this->device = $device;
		$this->experimentType = $experimentType;
	}

	public function run($input) {
		// We don't want to run multiple experiments
		// at the same time, on once device
		if($this->isRunningExperiment()) {
			throw new DeviceAlreadyRunningExperimentException;
		}

		// Validate the input
		$this->validateInput($input);


		event(new ExperimentStarted($this->device, $this->experimentType, $input));
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

	public function stopDevice() {
		$path = $this->getScriptPath("stop");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
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