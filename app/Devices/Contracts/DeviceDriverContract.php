<?php 

namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	const STATUS_OFFLINE = "offline";
	const STATUS_READY = "ready";
	const STATUS_EXPERIMENTING = "experimenting";

	const AVAILABLE_COMMANDS = ["init","change","start","read","status","stop"];
	
	public function availableCommands();
	public function checkCommandSupport($command);

}