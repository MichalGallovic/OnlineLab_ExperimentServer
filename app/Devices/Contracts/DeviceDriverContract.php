<?php namespace App\Devices\Contracts;

interface DeviceDriverContract
{
	public function read();
	public function run();
	public function stop();

	public function status();

}