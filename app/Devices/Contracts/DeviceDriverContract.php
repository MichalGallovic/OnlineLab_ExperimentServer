<?php namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	public function read();
	public function run($input);
	public function stop();

	public function status();

}