<?php

namespace App\Classes\Services;

use App\Device;
use App\Software;
use App\Classes\WebServer\Server;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\DeviceNotRunningExperimentException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;

/**
* Command Service
*/
class CommandService
{
	
	/**
	 * Available system commands
	 * @var array
	 */
	protected $comamnds;

	/**
	 * Requested Device
	 * @var App\Device
	 */
	protected $device;

	/**
	 * Requested Experiment
	 * @var App\Experiment
	 */
	protected $experiment;

	/**
	 * Requested Software name
	 * @var string
	 */
	protected $softwareName;

	/**
	 * Requested command
	 * @var string
	 */
	protected $commandName;

	/**
	 * Command input
	 * @var array
	 */
	protected $commandInput;

	/**
	 * Experiment Log 
	 * @var App\ExperimentLog
	 */
	protected $experimentLog;


	/**
	 * Files downloaded from webserver
	 * for experiment
	 * @var array
	 */
	protected $downloadedFiles;

	public function __construct(array $input, $deviceId)
	{
		$this->comamnds = DeviceDriverContract::AVAILABLE_COMMANDS;
		$this->device = Device::findOrFail($deviceId);
		$this->commandInput = $input;
		$this->downloadedFiles = [];
	}

	public function execute()
	{
		$deviceDriver = $this->resolveDeviceDriver();
		$this->commandName = $this->input('command');
		$deviceDriver->checkCommandSupport($this->commandName);
		$this->experiment = $this->device->getCurrentOrRequestedExperiment($this->softwareName);
		$this->experiment->validate($this->commandName, $this->input('input'));

		// If the request is from the server
		// check if we need to pre-download
		// regulators or schema files
		// from web server
	
		$input = $this->normalizeCommandInput($this->input('input'));

		if(method_exists($this, $this->commandName)) {
		    return $this->{$this->commandName}($deviceDriver, $input);
		}

		$commandMethod = strtolower($this->commandName) . "Command";

		if (App::environment() == 'local') {
		    $output = $deviceDriver->$commandMethod($input, 1);
		} else {
			$output = $deviceDriver->$commandMethod($input, $this->input("requested_by"));
		}	

		return $output;
	}

	protected function input($key = null)
	{
		return isset($this->commandInput[$key]) ? $this->commandInput[$key] : null;
	}

	protected function start(DeviceDriverContract $driver, $input)
	{
	    if (!is_null($this->device->currentExperiment)) {
	        throw new DeviceAlreadyRunningExperimentException;
	    }

		// On local dev environment, we are faking
		// who requested the command - user_id
		if (App::environment() == 'local') {
		    $driver->startCommand($input, 1);
		} else {
			$driver->startCommand($input, $this->input("requested_by"));
		}

		$this->device = $this->device->fresh();

		$this->experimentLog = $this->device->currentExperimentLogger;
		$result = is_null($this->experimentLog) ? null : $this->experimentLog->getResult();

		$this->device->detachCurrentExperiment();
		$this->device->detachPids();

		// Delete uploaded files
		foreach ($input as $name => $value) {
		    if($this->experiment->getInputType("start",$name) == "file") {
		        File::delete($value);
		    }
		}

		foreach ($this->downloadedFiles as $file) {
			if(File::exists($file)) {
				File::delete($file);
			}
		}

		return "Experiment ended";
	}

	protected function read(DeviceDriverContract $driver, $input)
	{
		return $driver->readCommand();
	}

	protected function status(DeviceDriverContract $driver, $input)
	{
		return $driver->statusCommand();
	}

	protected function stop(DeviceDriverContract $driver, $input)
	{
		if (is_null($this->device->currentExperimentLogger)) {
            throw new DeviceNotRunningExperimentException;
        }

		$driver->stopCommand();
		$this->device->detachCurrentExperiment();
		$this->device->detachPids();
	}

	protected function resolveDeviceDriver()
	{
		$software = Software::where('name', strtolower($this->input('software')))->first();
		$this->softwareName = !is_null($software) ? $software->name : null;        
		return $this->device->driver($this->softwareName);	
	}

	protected function normalizeCommandInput($inputs)
	{
		$inputs = isset($inputs) ? $inputs : [];

		$normalizedInputs = $inputs;

		$normalizedInputs = $this->downloadSchemas($inputs);

		// Normalize file inputs
		foreach ($normalizedInputs as $name => $value) {
		    if($this->isInputFile($this->commandName, $name)) {
		    	if(!File::exists($value)) {
		    		$filePath = storage_path("uploads/dev") . "/" . $value;
		    		$path = $filePath;
		    		$normalizedInputs[$name] = $path;
		    	}
		    }
		}

		return $normalizedInputs;
	}

	protected function downloadSchemas($inputs)
	{
		foreach ($inputs as $name => $value) {
		    if($this->isInputFile($this->commandName, $name) && $this->isUrl($value)) {
		    	$inputs[$name] = $this->downloadFile($value);
		    	$this->downloadedFiles[]=$inputs[$name];
		    }
		}
		return $inputs;
	}

	protected function downloadFile($url)
	{
		$server = new Server(config("webserver.ip"));
		return $server->download($url);
	}

	protected function isUrl($url)
	{
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	protected function isInputFile($command, $inputName)
	{
		return $this->experiment->getInputType($command,$inputName) == 'file';
	}

    /**
     * Gets the Requested command.
     *
     * @return string
     */
    public function getName()
    {
        return $this->commandName;
    }

    /**
     * Gets the Requested Device.
     *
     * @return App\Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Gets the Experiment Log.
     *
     * @return App\ExperimentLog
     */
    public function getExperimentLog()
    {
        return $this->experimentLog;
    }

    /**
     * Gets the Files downloaded from webserver
for experiment.
     *
     * @return array
     */
    public function getDownloadedFiles()
    {
        return $this->downloadedFiles;
    }
}