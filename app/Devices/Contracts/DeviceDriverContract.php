<?php namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	public function read();

	public function run($input, $requestedBy);
	public function isRunningExperiment();
	
	public function stop();

	public function status();

}