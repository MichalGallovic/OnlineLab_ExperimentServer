<?php

namespace App\Devices;

use App\Device;
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
     * Available commands per Experiment
     * @var array
     */
    protected $commands;

    public function __construct(Device $device, Experiment $experiment)
    {
        $this->device = $device;
        $this->experiment = $experiment;
        $this->commands = [];
    }

    protected function initCommand($commandType, $arguments)
    {

        $deviceType = $this->device->type->name;
        $softwareType = $this->experiment->software->name;

        $commandFactory = new CommandFactory($deviceType, $softwareType, $commandType, $this->scriptPaths);
        $method = $commandType . Str::upper($deviceType) . Str::ucfirst($softwareType);

        switch ($commandType) {
            case 'start':
            	$command = $commandFactory->$method($this->experiment, $arguments);
                break;
            case 'stop':
            	$command = $commandFactory->$method($this->experiment, $this->device);
                break;
            case 'read':
				$command = $commandFactory->$method($this->experiment, $this->device);
                break;
            case 'status': {
                $command = $commandFactory->$method($this->experiment, $this->device);
                break;
            }
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
            //@Todo if it is not command method, error normally
            $this->initCommand($method, $arguments);
            // Call first base class before method
            $beforeMethod = "before" . Str::ucfirst($method);
            $this->$beforeMethod($this->commands[$method]);
            // Then call its public concrete implementation
            call_user_func_array([$this, $method], [$this->commands[$method]]);
            // We do it like this, so developers don't have to call parent
            // methods manually, they will be called for them automatically
            $afterMethod = "after" . Str::ucfirst($method);
            return $this->$afterMethod($this->commands[$method]);
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

    protected function beforeRead(ReadCommand $command)
    {
    }

    protected function read(ReadCommand $command)
    {
    }

    protected function afterRead(ReadCommand $command)
    {
        return $command->getOutput();
    }

    protected function beforeStart(StartCommand $command)
    {
        $command->logToFile();
    }

    protected function start(StartCommand $command)
    {
    }

    protected function afterStart(StartCommand $command)
    {
        $command->stop();
    }

    protected function beforeStop(StopCommand $command)
    {
    }
    protected function stop(StopCommand $command)
    {
    }
    protected function afterStop(StopCommand $command)
    {
        return $command->stoppedSuccessfully();
    }

    protected function beforeInit(Command $command)
    {
    }
    protected function init($input)
    {
    }

    protected function afterInit()
    {
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

    protected function afterChange()
    {
    }

    protected function beforeStatus(Command $command)
    {
    }

    protected function status(Command $command)
    {
    }

    protected function afterStatus(Command $command)
    {
        return $command->getStatus();
    }
}
