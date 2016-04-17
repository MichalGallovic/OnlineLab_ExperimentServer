<?php

namespace App\Classes\Services;

use App\Device;
use App\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
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

	public function __construct(array $input, $deviceId)
	{
		$this->comamnds = DeviceDriverContract::AVAILABLE_COMMANDS;
		$this->device = Device::findOrFail($deviceId);
		$this->commandInput = $input;
	}

	public function execute()
	{
		$deviceDriver = $this->resolveDeviceDriver();
		$this->commandName = $this->input('command');
		$deviceDriver->checkCommandSupport($this->commandName);
		$this->experiment = $this->device->getCurrentOrRequestedExperiment($this->softwareName);
		$this->experiment->validate($this->commandName, $this->input('input'));
		$input = $this->normalizeCommandInput($this->input('input'));

		if(method_exists($this, $this->commandName)) {
		    return $this->{$this->commandName}($deviceDriver, $input);
		}

		$commandMethod = strtolower($this->commandName) . "Command";

		if (App::environment() == 'local') {
		    $output = $deviceDriver->$commandMethod($input, 1);
		} else {
		}

		return $output;
	}

	protected function input($key = null)
	{
		return isset($this->commandInput[$key]) ? $this->commandInput[$key] : null;
	}

	protected function start(DeviceDriverContract $driver, $input)
	{
		if(App::environment() != "local") {
		    if (!is_null($this->device->currentExperiment)) {
		        throw new DeviceAlreadyRunningExperimentException;
		    }
		}

		// On local dev environment, we are faking
		// who requested the command - user_id
		if (App::environment() == 'local') {
		    $driver->startCommand($input, 1);
		} else {
		}

		$this->device = $this->device->fresh();

		$logger = $this->device->currentExperimentLogger;
		$result = is_null($logger) ? null : $logger->getResult();

		$this->device->detachCurrentExperiment();
		$this->device->detachPids();

		// Delete uploaded files
		foreach ($input as $name => $value) {
		    if($this->experiment->getInputType("start",$name) == "file") {
		        File::delete($value);
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

		// Normalize file inputs
		foreach ($inputs as $name => $value) {
		    if($this->experiment->getInputType($this->commandName,$name) == "file") {
		        $filePath = storage_path("uploads/dev") . "/" . $value;
		        $path = $filePath;
		        $normalizedInputs[$name] = $path;
		    }
		}

		return $normalizedInputs;
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
}