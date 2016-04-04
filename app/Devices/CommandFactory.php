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
		} else {
			// throw expcetion
		}

		

		return call_user_func_array([$this, $method],$arguments);

	}

	protected function commandClassNamespace($concrete = "")
	{
		return $this->commandNamespace . "\\" . $concrete;
	}

	protected function scriptClassNamespace($concrete = "")
	{
		return $this->scriptsNamespace . "\\" . $concrete;
	}

	protected function commandClassExists()
	{
		$commandClassName = "App\\Devices\\Commands\\" . Str::upper($this->deviceType) . "\\" . Str::ucfirst($this->softwareType);
		return class_exists($commandClassName);
	}

	protected function scriptClassExists()
	{
		$scriptClassName = "App\\Devices\\Scripts\\" . Str::upper($this->deviceType) . "\\" . Str::ucfirst($this->softwareType);
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

	protected function stopCommand(Experiment $experiment, $arguments)
	{
	}

	protected function readCommand(Experiment $experiment, $arguments)
	{

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
		$command = new $commandClasss(
		    $experiment,
		    $readScript
		    );

		return $command;
	}


	protected function createLogger(Experiment $experiment, $arguments)
	{
		return new Logger($experiment, $arguments[0], $arguments[1]);
	}

	protected function createStartScript(Experiment $experiment, $scriptPath, $outputFilePath, $input)
	{
		// Check first certain file if exists
		return new \App\Devices\Scripts\StartScript($scriptPath, $experiment, $outputFilePath, $input);
	}

	protected function createStopScript(Experiment $experiment, $scriptPath)
	{
		return new \App\Devices\Scripts\StopScript($scriptPath, $experiment);	
	}

	// public function startTOS1AOpenloop(Experiment $experiment, $arguments)
	// {
	// 	$logger = $this->createLogger($experiment, $arguments);

	// 	$startScript = $this->createStartScript( 
	// 			$experiment,
	// 			$this->scriptPaths["start"],
	// 			$logger->getOutputFilePath(), 
	// 			$arguments[0]
	// 		);

	// 	$stopScript = $this->createStopScript(
	// 		$experiment,
	// 		$this->scriptPaths["stop"]
	// 		);

	//     $command = new \App\Devices\Commands\TOS1A\Openloop\StartCommand(
	//             $experiment,
	//             $startScript,
	//             $stopScript,
	//             $logger
	//         );
		
	// 	return $command;
	// }

	public function startTOS1AMatlab(Experiment $experiment, $arguments)
	{
		$logger = $this->createLogger($experiment, $arguments);
		$startScript = $this->createStartScript( 
				$experiment,
				$this->scriptPaths["start"],
				$logger->getOutputFilePath(), 
				$arguments[0]
			);
		$stopScript = new \App\Devices\Scripts\StopScript($this->scriptPaths["stop"], $experiment);
	    $command = new \App\Devices\Commands\TOS1A\Matlab\StartCommand(
	            $experiment,
	            $startScript,
	            $stopScript,
	            $logger
	        );
		
		return $command;
	}

	protected function readTOS1A(Experiment $experiment)
	{
		$readScript = new \App\Devices\Scripts\ReadScript(
		    $this->scriptPaths[$this->commandType],
		    $experiment
		    );
		$command = new \App\Devices\Commands\ReadCommand(
		    $experiment,
		    $readScript
		    );

		return $command;
	}

	// protected function statusTOS1A(Experiment $experiment)
	// {
	// 	$readScript = new \App\Devices\Scripts\ReadScript(
	// 	    $this->scriptPaths["read"],
	// 	    $experiment
	// 	    );
	// 	$command = new \App\Devices\Commands\StatusCommand(
	// 	    $experiment,
	// 	    $readScript
	// 	    );

	// 	return $command;
	// }

	protected function stopTOS1A(Experiment $experiment)
	{
    	$stopScript = new \App\Devices\Scripts\StopScript(
    		$this->scriptPaths[$this->commandType], 
    		$experiment
    		);
        $command = new \App\Devices\Commands\StopCommand(
                $experiment,
                $stopScript
            );

        return $command;
	}

	protected function stopTOS1AOpenloop(Experiment $experiment)
	{
		return $this->stopTOS1A($experiment);
	}

	// public function statusTOS1AOpenloop(Experiment $experiment)
	// {
	// 	return $this->statusTOS1A($experiment);
	// }

	public function readTOS1AOpenloop(Experiment $experiment)
	{
		return $this->readTOS1A($experiment);
	}

}