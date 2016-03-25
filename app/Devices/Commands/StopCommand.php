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

	public function __construct(Device $device, $path)
	{
		$this->device = $device;
		$this->stopScript = new StopScript($path, $this->device->port);
	}

	public function execute()
	{
		$this->device->status = DeviceDriverContract::STATUS_READY;
		$this->stopScript->run();

		$logger = $this->device->currentExperimentLogger;
		if(!is_null($logger)) {
			$logger->stopped_at = Carbon::now();
			$logger->save();
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
}