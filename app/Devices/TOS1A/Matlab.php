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
		// validation

		// process run experiment and pass data to python script

		// process start reading and writing data from tos1a
		$path = $this->getScriptPath("matlab");
		$process = $this->runProcessAsync($path);

		$isPidSet = false;
		$this->attachPid($process->getPid());
		$pids = [];
		$seconds = 0;
		while($process->isRunning()) {
			// if(!$isPidSet) {
				
			// 	$isPidSet = true;
			// }
			// try {
			// 	// When timeout is reached
			// 	// checkTimeout automatically stops the process
			// 	// and raises and exception
			// 	$process->checkTimeout();
			// } catch(ProcessTimedOutException $e) {
			// 	$pids = $this->stop();
			// }

			if($seconds > 5) {
				break;
			}

			usleep(1000000);
			$seconds++;
		}

		return $this->stopExperimentRunner($this->device->attached_pid);

		$this->detachPid();
		
		return $pids;
	}

	public function stop() {
		parent::stop();
		
		// stop the matlab pid
		return $this->stopExperimentRunner($this->device->attached_pid);
	}

	protected function attachPid($pid) {
		$this->device->attached_pid = $pid;
		$this->device->save();
	}

	protected function detachPid() {
		$this->attachPid(null);
	}

	protected function stopExperimentRunner($pid) {
		$process = new Process("pstree -p ". $pid ." | grep -o '([0-9]\+)' | grep -o '[0-9]\+'");
		 
		$process->run();
		$allProcesses = array_filter(explode("\n",$process->getOutput()));

		foreach ($allProcesses as $pid) {
			$arguments = [
				"-TERM",
				$pid
			];
			$process = $this->runProcess("kill",$arguments);
		}
	}
}