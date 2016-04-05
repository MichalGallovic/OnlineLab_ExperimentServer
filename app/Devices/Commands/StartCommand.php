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
	 * Experiment logger 
	 * @var App\Devices\Helpers\Logger
	 */
	protected $logger;


	/**
	 * Device reference (DB)
	 * @var App\Device
	 */
	protected $device;



	public function __construct(Experiment $experiment, Script $startScript, Script $stopScript, Logger $logger)
	{
		$this->device = $experiment->device;
		$this->startScript = $startScript;
		$this->stopScript = $stopScript;
		$this->logger = $logger;
	}


	public function execute()
	{
		$this->device->status = DeviceDriverContract::STATUS_EXPERIMENTING;
		$this->startScript->run();
		$this->device->attachPid($this->startScript->getPid());
		$this->device->save();
	}

	public function stop()
	{
		$this->startScript->stop();
		$this->startScript->cleanUp();

		$this->stopScript->run();
		
		$this->saveLog();
		$this->device->detachPids();

	}

	public function wait()
	{
		$this->startScript->waitOrTimeout();
	}

	protected function saveLog()
	{
		$this->logger->saveScript($this->startScript);
	}

	protected function setMeasuringRate($input)
	{
		$rate = $this->getMeasuringRate($input);
		$this->logger->setMeasuringRate($rate);
	}

	protected function setSimulationTime($input)
	{
		$time = $this->getSimulationTime($input);
		$this->logger->setSimulationTime($time);
		$this->startScript->setExecutionTime($time);
	}

	public function logToFile()
	{
		$this->logger->createLogFile();
	}


}