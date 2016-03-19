<?php 

namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	const STATUS_OFFLINE = "offline";
	const STATUS_READY = "ready";
	const STATUS_EXPERIMENTING = "experimenting";

	public function run($input);
	
	public function stop();

	public function status();

	public function isConnected();

    public function isReady();

    public function isRunningExperiment();

}