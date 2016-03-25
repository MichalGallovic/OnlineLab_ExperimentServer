<?php

namespace App\Devices\CUBE;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Openloop extends AbstractDevice implements DeviceDriverContract {

	//use AsyncRunnable;

	/**
     * Paths to read/stop/run scripts relative to
     * $scriptsPath (that is now "app_root"/server_scripts/"deviceName")
     * @var array
     */
	protected $scriptNames = [
        "read"	=> "",
        "stop"  => "",
        "start"	=> "",
        "init"  => "",
        "change"=> ""
    ];


    /**
     * Construct base class (App\Devices\AbstractDevice)
     * @param Device     $device     Device model from DB
     * @param Experiment $experiment Experiment model from DB
     */
	public function __construct(Device $device, Experiment $experiment)
	{
		parent::__construct($device,$experiment);
	}

	/**
	 * Is Device connected ?
	 * @return boolean
	 */
	public function isConnected()
	{
	    
	}

	/**
	 * Is Device ready ?
	 * @return boolean
	 */
	public function isReady()
	{
	    
	}

	/**
	 * Is Device running experiment ?
	 * @return boolean
	 */
	public function isRunningExperiment()
	{
	    
	}

	/**
	 * Getter for simulation time from User experiment input
	 * @param  array $input User experiment input
	 * @return mixed 	    Simulation time
	 */
	protected function getSimulationTime($input) {
		return $input[""];
	}

	/**
	 * Getter for measuring rate from User experiment input
	 * @param  array $input User experiment input
	 * @return mixed        Measuring rate
	 * Measuring rate is usually equals
	 * to the experiment sampling rate
	 */
	protected function getMeasuringRate($input) {
		return $input[""];
	}

}