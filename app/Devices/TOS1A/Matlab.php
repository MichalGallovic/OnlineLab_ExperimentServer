<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{

	public function __construct($device) {
		parent::__construct($device);
		$this->scriptNames["matlab"] = "matlab/run.py";
	}

	public function run() {
		if($this->isRunningExperiment()) {
			return $this->read();
		}

		// validation

		// process run experiment and pass data to python script

		// process start reading and writing data from tos1a
		$path = $this->getScriptPath("matlab");
		$process = $this->runProcessAsync($path);

		$this->attachPid($process->getPid());
		
		$seconds = 0;
		$runningStatusSet = false;
		while($process->isRunning()) {

			

			// Stuff with timeout
			// if($seconds > 5) {
			// 	break;
			// }

			// usleep(1000000);
			// $seconds++;
		}

		$this->stop();
		
		return $this->read();
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