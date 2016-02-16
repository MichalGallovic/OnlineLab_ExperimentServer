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
use App\Devices\AbstractDevice;
use Illuminate\Support\Facades\Validator;

abstract class AbstractTOS1A extends AbstractDevice
{
	// These @vars could be inside config file
	// and initialized in constructor

	protected $scriptsPath;

	protected $scriptNames = [
		"readonce" 		=> "readonce.py",
		"stop"     		=> "stop.py",
		"readexperiment"=> "readexperiment.py"
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
	
	protected $status;
	protected $output;
	protected $outputRetrieved;
	protected $assignedOutput;


	public function __construct($device) {
		parent::__construct($device);
		$this->scriptsPath = base_path() . "/server_scripts/TOS1A";
		$this->output = null;
		$this->assignedOutput = null;
		// The whole meaning of this class it to operate
		// on the physical device - so it is essential
		// that the device is connected
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
		// Stops matlab and cleans up all processes
		if(!is_null($this->device->attached_pids)) {
			$this->stopExperimentRunner();
		}
		// Stop the experiment on the physical device
		$this->stopDevice();
		// Detaches the main process pid from db
		$this->detachPids();
	}

	public function read() {
		$this->getDeviceOutput();
		$this->checkDeviceStatus();
		return $this->makeResponse();
	}

	public function status() {
		// vola read a parsuje to do array odpovede
		$this->getDeviceOutput();
		$this->checkDeviceStatus();
		return $this->status;
	}

	protected function readOnce() {

		$path = $this->getScriptPath("readonce");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
		$this->outputRetrieved = microtime(true)*1000;

		$this->output = $this->parseOutput($process->getOutput());
	}

	public function getDeviceOutput() {
		// Lazily instantiante the output
		// if it was not obtained, get it
		// upon first request or if the value was
		// retrieved before more than 200ms
		$now = microtime(true)*1000;
		$diffRetrieved = $now - $this->outputRetrieved;

		if(is_null($this->output)  || ($diffRetrieved > 100)) {
			$this->readOnce();
			$this->assignOutputToArguments();
		}

		return $this->output;
	}

	protected function isOffline() {
		// Empty array output is in all cases
		// error in python script or bad
		// port path or disconnected
		// device
		return empty($this->getDeviceOutput());
	}

	protected function isConnected() {
		if($this->isOffline()) return false;

		$this->assignOutputToArguments();

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

	protected function isStartingExperiment() {
		// When device is ready and pid is already attached
		// it means the experiment is initializing
		// (i.e. starting matlab takes some time)
		return $this->isReady() && !is_null($this->device->attached_pids);
	}

	protected function isExperimenting() {
		return ( $this->isConnected() && 
			floatval($this->assignedOutput["f_temp_int"]) != 0.0 );
	}

	protected function isRunningExperiment() {
		// device TOS1A responds with non zero filtered internal temperature
		// when running experiment
		return $this->isExperimenting() || $this->isStartingExperiment();
	}

	protected function assignOutputToArguments() {
		try {
			$this->assignedOutput = array_combine($this->outputArguments, $this->output);
		} catch(\Exception $e) {
			$this->assignedOutput = null;
		}
	}

	protected function stopDevice() {
		$path = $this->getScriptPath("stop");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
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

	public function checkDeviceStatus() {
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
	}

	protected function makeResponse() {
		return [
			"device_uuid" => $this->device->uuid,
			"device_type" => $this->device->device_type,
			"experiment_type"   => $this->device->experiment->name,
			"status" => $this->status,
			"output" => $this->assignedOutput
		];
	}

	protected function startReadingExperiment($time) {
		$path = $this->getScriptPath("readexperiment");
		$arguments = [
			$this->device->port,
			$this->device->uuid,
			$time,
			200
		];

		$process = $this->runProcessAsync($path, $arguments);

		return $process;
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
}