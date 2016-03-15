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
use App\ExperimentType;

abstract class AbstractTOS1A extends AbstractDevice
{

	protected $scriptsPath;

	protected $scriptNames = [
		"readonce" 		=> "readonce.py",
		"stop"     		=> "stop.py",
		"readexperiment"=> "readexperiment.py"
	];

	protected $outputArguments;
	
	protected $status;
	protected $output;
	protected $outputRetrieved;
	protected $assignedOutput;



	public function __construct($device, $experiment) {
		parent::__construct($device, $experiment);
		$this->scriptsPath = base_path() . "/server_scripts/tos1a";
		$this->output = null;
		$this->outputArguments = $experiment->getOutputArguments();
		// Defining input arguments and rules
		// for validation
		$this->inputArguments = $experiment->getInputRules();
		$this->assignedOutput = null;
		// The whole meaning of this class it to operate
		// on the physical device - so it is essential
		// that the device is connected
		if(!$this->isConnected()) {
			throw new DeviceNotConnectedException;
		}
	}

	/**
	 * Read TOS1A output in 2 steps
	 * The same reading method can be used for every
	 * TOS1A Software implementation
	 * 1. get output from physical device
	 * 2. query device / check output and deduce device status
	 * @todo Some SW environments as matlab can crash
	 * when read during experiment, so we could check befor ?
	 * @return array
	 */
	public function read() {
		$this->getDeviceOutput();
		$this->checkDeviceStatus();
		return $this->assignedOutput;
	}

	/**
	 * Read TOS1A and deduce status
	 * Very similar to @method read
	 * @return string
	 */
	public function status() {
		$this->getDeviceOutput();
		$this->checkDeviceStatus();
		return $this->status;
	}

	/**
	 * Read physical device output
	 * outputRetrieved marks last time 
	 * physical device was queried
	 */
	protected function readOnce() {

		$path = $this->getScriptPath("readonce");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);
		$this->outputRetrieved = microtime(true)*1000;

		$this->output = $this->parseOutput($process->getOutput());
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

	protected function assignOutputToArguments() {
		try {
			$this->assignedOutput = array_combine($this->outputArguments, $this->output);
		} catch(\Exception $e) {
			$this->assignedOutput = null;
		}
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
			$this->status = DeviceDriverContract::STATUS_EXPERIMENTING;
		} else if($this->isReady()) {
			$this->status = DeviceDriverContract::STATUS_READY;
			// When device is ready, we don't necesarilly
			// need to sent the output, but it could
			// be set on again just, by commenting
			// this out
			$this->assignedOutput = null;
		} else {
			$this->status = DeviceDriverContract::STATUS_OFFLINE;
		}
	}
}