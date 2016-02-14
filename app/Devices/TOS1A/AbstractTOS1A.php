<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Events\ProcessWasRan;
use App\Devices\Exceptions\DeviceNotConnectedException;
use App\Devices\Exceptions\DeviceNotReadyException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;
use App\Devices\Exceptions\ParametersInvalidException;

abstract class AbstractTOS1A
{
	// These @vars could be inside config file
	// and initialized in constructor

	protected $scriptsPath;
	protected $scriptNames = [
		"readonce" => "readonce.py",
		"stop"     => "stop.py"
	];

	protected $outputArguments = [
		"temp_chip",
		"f_temp_int",
		"d_temp_ext",
		"f_temp_ext",
		"d_temp_ext",
		"f_light_int_lin",
		"d_light_int_lin",
		"f_light_int_log",
		"d_light_int_log",
		"I_bulb",
		"V_bulb",
		"I_fan",
		"V_fan",
		"f_rpm",
		"d_rmp"
	];

	protected $rules;

	protected $device;
	protected $status;
	protected $output;
	protected $assignedOutput;
	protected $runtime;
	protected $validator;

	private $process;

	public function __construct($device) {
		$this->device = $device;
		$this->scriptsPath = base_path() . "/server_scripts/TOS1A";
		$this->output = null;

		// The whole meaning of this class it to operate
		// on the physical device - so it is essential
		// that it is connected
		if(!$this->isConnected()) {
			throw new DeviceNotConnectedException;
		}
	}

	public function run($input) {
		// We don't want to run multiple experiments
		// at the same time, on once device
		if($this->isRunningExperiment()) {
			throw new DeviceAlreadyRunningExperimentException;
		}

		// Validate the input
		$this->validateInput($input);
	}

	public function stop() {
		// run stop process + additional implementation
		// inside of each of concrete implementations
		// stop process that is running per matlab how ?

		$this->stopDevice();
	}

	public function read() {
		$this->readOnce();
		return $this->makeResponse();
	}

	public function readOnce() {
		$path = $this->getScriptPath("readonce");
		$arguments = [$this->device->port];

		$this->process = $this->runProcess($path, $arguments);

		$this->output = $this->parseOutput($this->process->getOutput());
	}

	protected function getOutput() {
		// Lazily instantiante the output
		// if it was not obtained, get it
		// upon first request
		if(is_null($this->output)) {
			$this->readOnce();
		}

		return $this->output;
	}

	protected function isOffline() {
		// Empty array output is in all cases
		// error in python script or bad
		// port path or disconnected
		// device
		return empty($this->getOutput());
	}

	protected function isConnected() {
		if($this->isOffline()) return false;

		$this->assignedOutput = array_combine($this->outputArguments, $this->output);

		// array_combine returns false, when number of 
		// keys and values does not match, in our case
		// that is when something went wrong
		return is_array($this->assignedOutput);
	}

	protected function isReady() {
		// device TOS1A responds with zero filtered internal temperature
		// if is connected and not running experiment
		return $this->isConnected() && floatval($this->assignedOutput["f_temp_int"]) == 0.0;
	}

	protected function isRunningExperiment() {
		// device TOS1A responds with non zero filtered internal temperature
		// when running experiment
		return ( $this->isConnected() && 
			floatval($this->assignedOutput["f_temp_int"]) != 0.0 ) ||
			$this->isStartingExperiment();
	}

	protected function isStartingExperiment() {
		// When device is ready and pid is already attached
		// it means the experiment is initializing
		// (i.e. starting matlab takes some time)
		return $this->isReady() && !is_null($this->device->attached_pid);
	}

	protected function stopDevice() {
		$path = $this->getScriptPath("stop");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
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

	protected function runProcessAsync($path, $arguments = [], $timeout = 20) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->setTimeout($timeout);
		$process->start();

		return $process;
	}

	protected function runProcessForceAsync($path, $arguments = []) {
		// $builder = new ProcessBuilder();
		// $builder->setPrefix($path);

		// // $arguments []= "> /dev/null";
		// // $arguments []= "2> /dev/null";
		// // $arguments []= "&";

		// $builder->setArguments($arguments);
		
		// $process = $builder->getProcess();
		$process = new Process($path . " > /dev/null 2> /dev/null &");
		$process->run();

		return $process;
	}

	protected function parseOutput($output) {
		$output = str_replace("$","", $output);
		$output = preg_replace("/\*(\w|\r|\n)*/", "", $output);

		$output = explode(",", $output);

		// When device was not connected or python script gave error
		// in case of bad port path etc. output at this
		// point was array with empty string
		// so lets filter it out completely
		return array_filter($output);
	}

	protected function makeResponse() {
		if($this->isRunningExperiment()) {
			$this->status = "experimenting";
		} else if($this->isReady()) {
			$this->status = "ready";
			// When device is ready, we don't necesarilly
			// need to sent the output, but it could
			// be set on again just, by commenting
			// this out
			$this->assignedOutput = null;
		} else {
			$this->status = "offline";
		}

		return [
			"device_uuid" => $this->device->uuid,
			"device_type" => $this->device->device_type,
			"experiment_type"   => $this->device->experiment->name,
			"status" => $this->status,
			"output" => $this->assignedOutput
		];
	}

	protected function createStatusAndOutput($output, $process) {
		$this->device->fresh();

		if(!$process->isSuccessful()) {
			$this->status = "offline";
			return;
		}

		if(count($this->outputArguments) != count($output)) {
			$this->status = "offline";
			return;
		}

		if($this->device->status == "initializing_experiment") {
			$this->status = "initializing_experiment";
			return;
		}

		if($this->device->status == "experimenting") {
			if(floatval($output["f_temp_int"]) == 0.00) {
				// $this->status = ""
			}
		}

		

		$output = array_combine($this->outputArguments, $output);


		if(floatval($output["f_temp_int"]) == 0.00) {
			$this->changeStatus("ready");
		}

		// Otherwise the experiment is ON!!!
		if($this->device->status == "experimenting") {
			$this->output = $output;
		}


		// switch ($this->device->status) {
		// 	case 'experimenting':
		// 		$this->output = $output;
		// 		break;
		// 	case ""
		// }



		// if(count($this->outputArguments) != count($output)) {
		// 	$this->status = "offline";
		// } else {
			

		// 	if(floatval($output['f_temp_int']) == 0.00) {
		// 		if($this->device->status == "initializing_experiment") {
		// 			$this->status = "initializing_experiment";
		// 			return;
		// 		}
		// 		$this->status = "ready";
		// 	} else {
		// 		$this->status = "experimenting";
		// 		$this->output = $output;
		// 	}
		// }
	}

	protected function changeStatus($status) {
		$this->device->status = $status;
		$this->device->save();
	}

	public function readExperiment() {
		// read contents of a file
	}

	public function status() {
		// vola read a parsuje to do array odpovede
		$output = $this->readOnce();


	}
}