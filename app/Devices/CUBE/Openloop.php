<?php

namespace App\Devices\CUBE;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Openloop extends AbstractDevice implements DeviceDriverContract {


	/**
     * Paths to read/stop/run scripts relative to
     * $(app_root)/server_scripts folder
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

}