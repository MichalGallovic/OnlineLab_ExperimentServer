<?php

namespace App\Devices\Commands;

use App\Experiment;
use App\Devices\Commands\Command;
use App\Devices\Scripts\ReadScript;
use App\Devices\Contracts\DeviceDriverContract;

class StatusCommand extends ReadCommand
{

	/**
	 * Device DB
	 * @var App\Device
	 */
	protected $device;

	public function __construct(Experiment $experiment, ReadScript $script)
	{
		parent::__construct($experiment, $script);
		$this->device = $experiment->device;

	}

	public function execute()
	{
		parent::execute();
	}

	public function getStatus()
	{
		$status = null;
		if ($this->isRunningExperiment()) {
		    $status = DeviceDriverContract::STATUS_EXPERIMENTING;
		} elseif ($this->isReady()) {
		    $status = DeviceDriverContract::STATUS_READY;
		} else {
		    $status = DeviceDriverContract::STATUS_OFFLINE;
		}

		return $status;
	}

	protected function isConnected()
	{
	    $output = $this->output;
	    
	    if (empty($output)) {
	        return false;
	    }

	    // array_combine returns false, when number of 
	    // keys and values does not match, in our case
	    // that is when something went wrong
	    return is_array($output);
	}

	protected function isReady()
	{
	    // device TOS1A responds with zero filtered internal temperature
	    // if is connected and not running experiment
	    return $this->isConnected() && floatval($this->output["f_temp_int"]) == 0.0;
	}

	protected function isRunningExperiment()
	{
	    // device TOS1A responds with non zero filtered internal temperature
	    // when running experiment
	    return $this->isAlreadyExperimenting() || $this->isStartingExperiment();
	}

	/**
	 * Helper functions
	 */

	protected function isStartingExperiment()
	{
	    // When device is ready and pid is already attached
	    // it means the experiment is initializing
	    // (i.e. starting matlab takes some time)
	    return $this->isReady() && !is_null($this->device->attached_pids);
	}

	protected function isAlreadyExperimenting()
	{
	    return ($this->isConnected($this->output) &&
	        floatval($this->output["f_temp_int"]) != 0.0);
	}

}