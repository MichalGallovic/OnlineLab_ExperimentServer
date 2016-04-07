<?php

namespace App\Devices;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use Illuminate\Support\Str;
use App\Devices\CommandFactory;
use App\Devices\Helpers\Logger;
use App\Devices\Commands\Command;
use App\Devices\Scripts\ReadScript;
use App\Devices\Scripts\StopScript;
use App\Devices\Scripts\StartScript;
use App\Devices\Commands\InitCommand;
use App\Devices\Commands\ReadCommand;
use App\Devices\Commands\StopCommand;
use App\Devices\Commands\StartCommand;
use App\Devices\Commands\ChangeCommand;
use App\Devices\Commands\StatusCommand;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\CommandTypeNotSupported;
use App\Devices\Exceptions\ExperimentCommandNotAvailable;

abstract class AbstractDevice
{
    /**
     * Paths to read/stop/run scripts relative to
     * $scriptsPath
     * @var array
     */
    protected $scriptPaths;
 
    /**
     * Device model (from DB)
     * @var App\Device
     */
    protected $device;

    /**
     * Experiment model (from DB)
     * @var App\Experiment
     */
    protected $experiment;

    /**
     * Experiment log model (from DB)
     * @var App\ExperimentLog
     */
    protected $experimentLog;
    /**
     * Available commands per Experiment
     * @var array
     */
    protected $commands;

    public function __construct(Device $device, Experiment $experiment)
    {
        $this->device = $device;
        $this->experiment = $experiment;
        // Can be null, when experiment is nut running
        $this->experimentLog = $device->currentExperimentLogger;
        $this->commands = [];
    }

    protected function initCommand($commandType, $arguments)
    {

        $deviceType = $this->device->type->name;
        $softwareType = $this->experiment->software->name;

        $commandFactory = new CommandFactory($deviceType, $softwareType, $commandType, $this->scriptPaths);
        
        switch ($commandType) {
            case 'start':
            	$command = $commandFactory->startCommand($this->experiment, $arguments);
                break;
            case 'stop':
            	$command = $commandFactory->stopCommand($this->experiment, $arguments);
                break;
            case 'read':
				$command = $commandFactory->readCommand($this->experiment, $arguments);
                break;
            case 'status':
                $command = $commandFactory->statusCommand($this->experiment, $arguments);
                break;
            case 'init':
            	$command = $commandFactory->initCommand($this->experiment, $arguments);
            	break;
        }

        $this->commands[$commandType] = $command;
    }

    /**
     * Magic method for command interface
     * @param  string $method    Method name
     * @param  array $arguments  Array of arguments
     */
    public function __call($method, $arguments)
    {
        $availableCommands = DeviceDriverContract::AVAILABLE_COMMANDS;
        $commandMethods = [];
        
        // so the commands can be called with:
        // start -> startCommand method
        // init -> initCommand method etc.
        foreach ($availableCommands as $command) {
            $key = $command . "Command";
            $commandMethods [$key]= $command;
        }

        if (in_array($method, array_keys($commandMethods))) {
            $method = $commandMethods[$method];
            $reflector = new \ReflectionClass($this);
            $check = $reflector->getMethod($method);

            if ($check->class == get_class()) {
                throw new ExperimentCommandNotAvailable(
                    $this->experiment,
                    Str::ucfirst($method)
                );
            }
            $arguments = empty($arguments) ? [[]] : $arguments;
            //@Todo if it is not command method, error normally
            // $this->initCommand($method, $arguments);
            // Call first base class before method
            $beforeMethod = "before" . Str::ucfirst($method);
            $resultBefore = call_user_func_array([$this, $beforeMethod], $arguments);

            if(!is_null($resultBefore)) {
                $arguments []= $resultBefore;
            }
            // Then call its public concrete implementation
            $result = call_user_func_array([$this, $method], $arguments);

            if(!is_null($result)) {
                $arguments []= $result;
            }

            // We do it like this, so developers don't have to call parent
            // methods manually, they will be called for them automatically
            $this->device = $this->device->fresh();
            $afterMethod = "after" . Str::ucfirst($method);
            return call_user_func_array([$this, $afterMethod], $arguments);
        }
    }

    public function checkCommandSupport($command)
    {
    	// Normalize command name
    	$command = strtolower($command);
    	$availableCommands = DeviceDriverContract::AVAILABLE_COMMANDS;

    	$reflector = new \ReflectionClass($this);
    	try {
    		$check = $reflector->getMethod($command);
    	} catch(\ReflectionException $e) {
    		throw new CommandTypeNotSupported($command);
    	}

    	if ($check->class == get_class()) {
    	    throw new ExperimentCommandNotAvailable(
    	        $this->experiment,
    	        Str::ucfirst($command)
    	    );
    	}
    }

    public function availableCommands()
    {
        $reflector = new \ReflectionClass($this);
        $commands = DeviceDriverContract::AVAILABLE_COMMANDS;
        $availableCommands = [];

        foreach ($commands as $command) {
            $check = $reflector->getMethod($command);
            if ($check->class != get_class()) {
                $availableCommands []= $command;
            }
        }

        return $availableCommands;
    }

    protected function beforeRead($input)
    {
    }

    protected function read($input)
    {
    }

    protected function afterRead($input, $output)
    {
        $arguments = $this->experiment->getOutputArguments();

        try {
            $combinedOutput = array_combine($arguments, $output);
        } catch (\Exception $e) {

        }

        return $combinedOutput;
    }

    protected function beforeStart($input, $requestedBy)
    {
        // Before start command, we want to create log file
        // where experiment can write its output
        // and set up App\ExperimentLog - model of
        // experiment_logs table
        $duration = $this->parseDuration($input);
        if(is_null($duration)) {
            throw new \Exception('Please implement parseDuration($input) method, if you use start command.');
        }
        $rate = $this->parseSamplingRate($input);
        if(is_null($rate)) {
            throw new \Exception('Please implement parseSamplingRate($input) method, if you use start command.');
        }

        $logger = new Logger($this->experiment, $input, $requestedBy);
        $logger->setSimulationTime($duration);
        $logger->setMeasuringRate($rate);
        $logger->createLogFile();
        $this->device = $this->device->fresh();
        $this->experimentLog = $this->device->currentExperimentLogger;
    }

    protected function start($input)
    {

    }

    protected function afterStart($input)
    {
        $script = new StopScript(
                $this->scriptPaths["stop"],
                $this->device
            );

        $script->run();
    }

    protected function beforeStop($input)
    {
    }
    protected function stop($input)
    {
    }
    protected function afterStop($input)
    {
        $this->experimentLog->stopped_at = Carbon::now();
        $this->experimentLog->save();
    }

    protected function beforeInit($input)
    {
    }
    protected function init($input)
    {
    }

    protected function afterInit($input, $requestedBy, $output)
    {
        return $output;
    }


    protected function beforeChange($input)
    {
    }
    /**
     * Change experiment input parameters
     * while experiment is running
     * @param  array $input User experiment input
     */
    protected function change($input)
    {
    }

    protected function afterChange($input, $requestedBy, $output)
    {
        return $output;
    }

    protected function beforeStatus($input)
    {
    }

    protected function status($input)
    {
    }

    protected function afterStatus($input)
    {
        // return $command->getStatus();
    }
}
