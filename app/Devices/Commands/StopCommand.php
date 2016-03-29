<?php

namespace App\Devices\Commands;

use App\Device;
use Carbon\Carbon;
use App\Devices\Commands\Command;
use App\Devices\Scripts\StopScript;
use App\Devices\Contracts\DeviceDriverContract;

class StopCommand extends Command
{
	/**
	 * Command name
	 * @var string
	 */
	protected $name = "start";

	/**
	 * Stop script
	 * @var StopScript
	 */
	protected $stopScript;	

	/**
	 * Device reference (DB)
	 * @var App\Device
	 */
	protected $device;
	protected $logger;

	public function __construct(Device $device, StopScript $script)
	{
		$this->device = $device;
		$this->stopScript = $script;

		$this->logger = null;
	}

	public function execute()
	{
		$this->device->status = DeviceDriverContract::STATUS_READY;
		$this->stopScript->run();

		$this->logger = $this->device->currentExperimentLogger;
		if(!is_null($this->logger)) {
			$this->logger->stopped_at = Carbon::now();
			$this->logger->save();
		} 



		// run sth that will also kill all child processes
		$pids = json_decode($this->device->attached_pids);
		$this->stopScript->cleanUp($pids);


		$this->device->detachPids();
		$this->device->detachCurrentExperiment();
	}

	public function stop()
	{

	}

	public function stoppedSuccessfully()
	{
		return !is_null($this->logger) ? isset($this->logger->stopped_at) : false;
	}
}