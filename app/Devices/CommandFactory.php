<?php

namespace App\Devices;

use App\Device;
use App\Experiment;
use Illuminate\Support\Str;
use App\Devices\Helpers\Logger;
use App\Devices\Scripts\StartScript;

/**
*  Factory for creating Command instances
*/
class CommandFactory
{
	protected $deviceType;
	protected $softwareType;
	protected $commandType;
	protected $scriptPaths;
	protected $commandsNamespace = "App\\Devices\\Commands";
	protected $scriptsNamespace = "App\\Devices\\Scripts";

	public function __construct($deviceType, $softwareType, $commandType, $scriptPaths)
	{
		$this->deviceType = $deviceType;
		$this->softwareType = $softwareType;
		$this->commandType = $commandType;
		$this->scriptPaths = $scriptPaths;
	}

	protected function createLogger(Experiment $experiment, $arguments)
	{
		return new Logger($experiment, $arguments[0], $arguments[1]);
	}

	public function __call($method, $arguments)
	{
		$deviceType = Str::upper($this->deviceType);
		$softwareType = Str::ucfirst($this->softwareType);
		$concreteMethod = $this->commandType . $deviceType . $softwareType;

		// When we use concrete experiment command and have method 
		// i.e. like startTOS1AMatlab it will be called instead
		// of the general implementation
		if(method_exists($this, $concreteMethod)) {
			return call_user_func_array([$this, $concreteMethod], $arguments);
		}

		if($this->commandClassExists() && $this->scriptClassExists()) {
			$arguments []= $this->commandClassNamespace($deviceType . "\\" . $softwareType . "\\");
			$arguments []= $this->scriptClassNamespace($deviceType . "\\" . $softwareType . "\\");
		} else if ($this->commandClassExists() && !$this->scriptClassExists()) {
			$arguments []= $this->commandClassNamespace($deviceType . "\\" . $softwareType . "\\");
			$arguments []= $this->scriptClassNamespace();
		} else if (!$this->commandClassExists() && $this->scriptClassExists()) {
			$arguments []= $this->commandClassNamespace();
			$arguments []= $this->scriptClassNamespace($deviceType . "\\" . $softwareType . "\\");
		} else if(!$this->commandClassExists() && !$this->scriptClassExists()) {
			$arguments []= $this->commandClassNamespace();
			$arguments []= $this->scriptClassNamespace();
		}

		return call_user_func_array([$this, $method],$arguments);

	}

	protected function commandClassNamespace($concrete = "")
	{
		return $this->commandsNamespace . "\\" . $concrete;
	}

	protected function scriptClassNamespace($concrete = "")
	{
		return $this->scriptsNamespace . "\\" . $concrete;
	}

	protected function commandClassExists()
	{
		$commandClassName = "App\\Devices\\Commands\\" . Str::upper($this->deviceType) . "\\" . Str::ucfirst($this->softwareType) . "\\" . Str::ucfirst($this->commandType) . "Command";

		return class_exists($commandClassName);
	}

	protected function scriptClassExists()
	{
		$scriptClassName = "App\\Devices\\Scripts\\" . Str::upper($this->deviceType) . "\\" . Str::ucfirst($this->softwareType) . "\\" . Str::ucfirst($this->commandType) . "Script";
		return class_exists($scriptClassName);
	}

	protected function startCommand(Experiment $experiment, $arguments, $commandPrefix, $scriptPrefix)
	{
		$logger = $this->createLogger($experiment, $arguments);

		$startScriptClass = $scriptPrefix . "StartScript";
		$startScript = new $startScriptClass( 
				$experiment,
				$this->scriptPaths["start"],
				$logger->getOutputFilePath(), 
				$arguments[0]
			);

		$stopScriptClass = $scriptPrefix . "StopScript";
		$stopScript = new $stopScriptClass(
			$experiment,
			$this->scriptPaths["stop"]
			);

		$commandClass = $commandPrefix . "StartCommand";
	    $command = new $commandClass(
	            $experiment,
	            $startScript,
	            $stopScript,
	            $logger
	        );

		return $command;
	}

	protected function stopCommand(Experiment $experiment, $arguments, $commandPrefix, $scriptPrefix)
	{
		$stopScriptClass = $scriptPrefix . "StopScript";
		$stopScript = new \App\Devices\Scripts\StopScript(
    		$experiment,
    		$this->scriptPaths[$this->commandType] 
    		);

		$commandClass = $commandPrefix . "StopCommand";
        $command = new \App\Devices\Commands\StopCommand(
                $experiment,
                $stopScript
            );

        return $command;
	}

	protected function readCommand(Experiment $experiment, $arguments, $commandPrefix, $scriptPrefix)
	{
		$readScriptClass = $scriptPrefix . "ReadScript";
		$readScript = new $readScriptClass(
		    $this->scriptPaths[$this->commandType],
		    $experiment
		    );

		$commandClass = $commandPrefix . "ReadCommand";
		$command = new $commandClass(
		    $experiment,
		    $readScript
		    );

		return $command;
	}

	protected function initCommand(Experiment $experiment, $arguments)
	{

	}

	protected function changeCommand(Experiment $experiment, $arguments)
	{

	}

	protected function statusCommand(Experiment $experiment, $arguments,  $commandPrefix, $scriptPrefix)
	{
		$readScriptClass = $scriptPrefix . "ReadScript"; 
		$readScript = new $readScriptClass(
		    $this->scriptPaths["read"],
		    $experiment
		    );

		$commandClass = $commandPrefix . "StatusCommand";
		$command = new $commandClass(
		    $experiment,
		    $readScript
		    );

		return $command;
	}



}