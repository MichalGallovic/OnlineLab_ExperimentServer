<?php

namespace App\Devices;

use App\Device;
use App\Experiment;
use Illuminate\Support\Str;
use App\Devices\Commands\Command;
use App\Devices\Scripts\ReadScript;
use App\Devices\Commands\InitCommand;
use App\Devices\Commands\ReadCommand;
use App\Devices\Commands\StopCommand;
use App\Devices\Commands\StartCommand;
use App\Devices\Commands\ChangeCommand;
use App\Devices\Commands\StatusCommand;
use App\Devices\Contracts\DeviceDriverContract;

abstract class AbstractDevice
{
    /**
     * Paths to read/stop/run scripts relative to
     * $scriptsPath
     * @var array
     */
    protected $scriptNames;
 
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
     * Experiment user input
     * @method getSimulationTime
     * @method getMeasuringRate
     * @var array
     */
    protected $experimentInput;

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
        $this->scriptsPath = $this->generateScriptsPath();
    }

    protected function initCommand($type, $arguments)
    {
    	$command = null;
    	//@Todo Some check here ?
    	switch ($type) {
    		case 'start': {
    			$command = new StartCommand(
    					$this->experiment,
    					$this->scriptNames[$type],
    					$arguments[0],
    					$arguments[1]
    				);
    			$command->setStopScript($this->scriptNames["stop"]);
    			break;
    		}
    		case 'stop': {
    			$command = new StopCommand(
    				$this->device,
    				$this->scriptNames[$type]
    				);
    			break;
    		}
    		case 'read': {
    			$script = new ReadScript(
    				$this->scriptNames[$type],
    				$this->device
    				);
    			$command = new ReadCommand(
    				$this->experiment,
    				$script
    				);
    			break;
    		}
    		case 'status': {
    			$script = new ReadScript(
    				$this->scriptNames["read"],
    				$this->device
    				);
    			$command = new StatusCommand(
    				$this->experiment,
    				$script
    				);
    			break;
    		}
    	}

    	$this->commands[$type] = $command;
    }

   

    /**
     * Abstract methods to implement - also check 
     * App\Devices\Contracts\DeviceDriverContract
     * to see public interface that has to be implemented
     */

    /**
     * Get simulation time has to be implemented
     * per experiment basis, because simulation
     * time is deduced from the input arguments
     * 
     * @return int
     */
    abstract protected function getSimulationTime($input);

    /**
     * Get measuring rate (usually equals to the sampling)
     * time - number which tells how often results are
     * measured
     * @return int
     */
    abstract protected function getMeasuringRate($input);


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
            if ($check->class == get_called_class()) {
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
    	$command->setMeasuringRate($this->getMeasuringRate($this->experimentInput));
    	$command->setSimulationTime($this->getSimulationTime($this->experimentInput));
    	$command->logToFile();
    }

    protected function start(StartCommand $command)
    {
    	
    }

    protected function afterStart(StartCommand $command)
    {
        $command->stop();
     	$logger = $command->getExperimentLogger();
     	return $logger->fresh();
    }

    protected function beforeStop(StopCommand $command)
    {

    }
    protected function stop(StopCommand $command)
    {

    }
    protected function afterStop(StopCommand $command)
    {
    	return "stopped like a boss";
    }

    protected function beforeInit($input)
    {
    }
    /**
     * Initialize experiment method
     * @param  array $input User experiment input
     */
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

  
    public function wasForceStopped()
    {
        if (is_null($this->device->currentExperimentLogger)) {
            return true;
        }
        
        return !is_null($this->device->currentExperimentLogger->stopped_at);
    }

    public function wasTimedOut()
    {
        return !is_null($this->experimentLogger->fresh()->timedout_at);
    }

    protected function isLoggingExperiment()
    {
        $this->device = $this->device->fresh();
        return !is_null($this->device->currentExperimentLogger);
    }   
   
}
