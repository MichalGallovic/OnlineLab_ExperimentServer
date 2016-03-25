<?php

namespace App\Devices\Commands;

use App\Experiment;
use App\Devices\Helpers\Logger;
use App\Devices\Scripts\Script;
use App\Devices\Commands\Command;
use App\Events\ExperimentStarted;
use App\Devices\Scripts\StopScript;
use App\Devices\Scripts\StartScript;
use App\Devices\Contracts\DeviceDriverContract;
/**
* StartCommand
*/
class StartCommand extends Command
{

	/**
	 * Command name
	 * @var string
	 */
	protected $name = "start";
	/**
	 * Start script
	 * @var App\Devices\Scripts\StartScript
	 */
	protected $startScript;

	/**
	 * Stop script
	 * @var StopScript
	 */
	protected $stopScript;

	/**
	 * Experiment Model (DB)
	 * @var App\Experiment
	 */
	protected $experiment;

	/**
	 * Experiment input
	 * @var array
	 */
	protected $input;

	/**
	 * Experiment logger 
	 * @var App\Devices\Helpers\Logger
	 */
	protected $logger;


	/**
	 * Device reference (DB)
	 * @var App\Device
	 */
	protected $device;

	/**
	 * Software reference (DB)
	 * @var App\Software
	 */
	protected $software;


	public function __construct(Experiment $experiment, $path, $input, $requestedBy)
	{
		$this->experiment = $experiment;
		$this->device = $experiment->device;
		$this->software = $experiment->software;
		$this->logger = new Logger($experiment, $input);
		$this->setRequestedBy($requestedBy);
		$this->startScript = new StartScript(
				$path, 
				$this->device->port, 
				$this->logger->getOutputFilePath(), 
				$input
		);
	}

	public function setStopScript($path)
	{
		$this->stopScript = new StopScript($path, $this->device->port);
	}

	public function execute()
	{
		$this->device->status = DeviceDriverContract::STATUS_EXPERIMENTING;
		$this->startScript->run();
		$this->device->attachPid($this->startScript->getPid());
	}

	public function stop()
	{
		$this->startScript->stop();
		$this->startScript->cleanUp();

		$this->stopScript->run();
		
		$this->device->detachPids();
		$this->device->detachCurrentExperiment();
	}

	public function wait()
	{
		$this->startScript->waitOrTimeout();
	}

	public function saveLog()
	{
		$this->logger->saveScript($this->startScript);
	}

	public function setInput(array $input)
	{
		$this->input = $input;
	}

	public function setMeasuringRate($rate)
	{
		$this->logger->setMeasuringRate($rate);
	}

	public function setSimulationTime($time)
	{
		$this->logger->setSimulationTime($time);
		$this->startScript->setExecutionTime($time);
	}

	public function setRequestedBy($userId)
	{
		$this->logger->setRequestedBy($userId);
	}

	public function logToFile()
	{
		$this->logger->createLogFile();
	}

	public function getScriptPath()
	{
		return $this->startScript->getPath();
	}

	public function getExperimentLogger()
	{
		return $this->logger->getExperimentLogger();
	}

}