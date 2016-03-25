<?php

namespace App\Devices\Commands;

use App\Experiment;
use App\Devices\Helpers\Logger;
use App\Devices\Scripts\Script;
use App\Devices\Commands\Command;
use App\Events\ExperimentStarted;
use App\Devices\Scripts\StartScript;
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
	protected $script;

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


	public function __construct(Experiment $experiment, $path, $input)
	{
		$this->experiment = $experiment;
		$this->device = $experiment->device;
		$this->software = $experiment->software;
		$this->script = new StartScript($path, $input);
		$this->logger = new Logger($experiment, $this->script);
	}

	public function execute()
	{
		$this->logger->save();
		$this->device->status = DeviceDriverContract::STATUS_EXPERIMENTING;
		$this->script->run();
		$pids = $this->script->getPids();
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
		return $this->script->getPath();
	}

	public function getExperimentLogger()
	{
		return $this->logger->getExperimentLogger();
	}

}