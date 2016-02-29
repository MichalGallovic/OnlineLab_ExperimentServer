<?php 

namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	public function read();

	public function run($input, $requestedBy);
	
	public function stop();

	public function status();

	public function getInputArguments();

}