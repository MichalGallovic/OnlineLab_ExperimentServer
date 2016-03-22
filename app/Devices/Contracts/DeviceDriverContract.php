<?php 

namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	const STATUS_OFFLINE = "offline";
	const STATUS_READY = "ready";
	const STATUS_EXPERIMENTING = "experimenting";

	const AVAILABLE_COMMANDS = ["init","change","start","stop"];
	
	public function stop();

	public function status();

    public function isExperimenting();

}