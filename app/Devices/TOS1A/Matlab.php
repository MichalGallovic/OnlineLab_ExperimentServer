<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Validator;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Events\ProcessWasRan;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{

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
		
		// process start reading and writing data from tos1a

		$seconds = 0;
		while($process->isRunning()) {
			// check some stuff with timeout
			// experiment initialization
		}

		event(new ProcessWasRan($process,$this->device));

		$this->stop();
		
		return $this->read();
	}

	protected function runExperiment($arguments) {
		$path = $this->getScriptPath("matlab");

		// matlab starts 15s this should be automated
		$timeout = $arguments["t_sim"] + 20;
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
		if(!is_null($this->device->attached_pid)) {
			$this->stopExperimentRunner($this->device->attached_pid);
		}
		// Stop the experiment on the physical device
		$this->stopDevice();
		// Detaches the main process pid from db
		$this->detachPid();
	}

	protected function attachPid($pid) {
		$this->device->attached_pid = $pid;
		$this->device->save();
	}

	protected function detachPid() {
		$this->attachPid(null);
	}

	protected function stopExperimentRunner($pid) {
		$pids = $this->getAllChildProcesses($pid);

		// Kill all processes created for experiment running
		foreach ($pids as $pid) {
			$arguments = [
				"-TERM",
				$pid
			];
			$process = $this->runProcess("kill",$arguments);
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