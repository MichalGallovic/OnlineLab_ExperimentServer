<?php 

namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	const STATUS_OFFLINE = "offline";
	const STATUS_READY = "ready";
	const STATUS_EXPERIMENTING = "experimenting";

	public function read();

	public function run($input, $requestedBy);
	
	public function stop();

	public function status();

}