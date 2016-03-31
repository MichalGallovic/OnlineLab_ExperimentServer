<?php

namespace App\Devices;

use App\Device;
use App\Experiment;
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


	public function startTOS1AOpenloop(Experiment $experiment, $arguments)
	{
		$logger = $this->createLogger($experiment, $arguments);
		$startScript = new \App\Devices\Scripts\StartScript(
				$this->scriptPaths["start"], 
				$experiment->device, 
				$logger->getOutputFilePath(), 
				$arguments[0]
			);

		$stopScript = new \App\Devices\Scripts\StopScript(
			$this->scriptPaths["stop"], 
			$experiment->device
			);

	    $command = new \App\Devices\Commands\TOS1A\Openloop\StartCommand(
	            $experiment,
	            $startScript,
	            $stopScript,
	            $logger
	        );
		
		return $command;
	}

	public function startTOS1AMatlab(Experiment $experiment, $arguments)
	{
		$logger = $this->createLogger($experiment, $arguments);
		$startScript = new \App\Devices\Scripts\StartScript(
				$this->scriptPaths["start"], 
				$experiment->device, 
				$logger->getOutputFilePath(), 
				$arguments[0]
			);
		$stopScript = new \App\Devices\Scripts\StopScript($this->scriptPaths["stop"], $experiment->device);
	    $command = new \App\Devices\Commands\TOS1A\Matlab\StartCommand(
	            $experiment,
	            $startScript,
	            $stopScript,
	            $logger
	        );
		
		return $command;
	}

	protected function readTOS1A(Experiment $experiment, Device $device)
	{
		$script = new \App\Devices\Scripts\ReadScript(
		    $this->scriptPaths[$this->commandType],
		    $device
		    );
		$command = new \App\Devices\Commands\ReadCommand(
		    $experiment,
		    $script
		    );

		return $command;
	}

	protected function statusTOS1A(Experiment $experiment, Device $device)
	{
		$script = new \App\Devices\Scripts\ReadScript(
		    $this->scriptPaths["read"],
		    $device
		    );
		$command = new \App\Devices\Commands\StatusCommand(
		    $experiment,
		    $script
		    );

		return $command;
	}

	protected function stopTOS1A(Experiment $experiment, Device $device)
	{
    	$stopScript = new \App\Devices\Scripts\StopScript(
    		$this->scriptPaths[$this->commandType], 
    		$device
    		);
        $command = new \App\Devices\Commands\StopCommand(
                $device,
                $stopScript
            );

        return $command;
	}

	public function stopTOS1AOpenloop(Experiment $experiment, Device $device)
	{
		return $this->stopTOS1A($experiment, $device);
	}

	public function statusTOS1AOpenloop(Experiment $experiment, Device $device)
	{
		return $this->statusTOS1A($experiment, $device);
	}

	public function readTOS1AOpenloop(Experiment $experiment, Device $device)
	{
		return $this->readTOS1A($experiment, $device);
	}

}