<?php namespace App\Devices;

use App\Devices\TOS1A\Loop;
use App\Devices\TOS1A\Openmodelica;
use App\Devices\TOS1A\Scilab;
use App\Devices\TOS1A\Matlab;
use App\Device;

/**
 * Manages the instatiation strategy
 * of experiment 
 */
class DeviceManager
{
	protected $device;

	/**
	 * @param App\Device
	 */
	public function __construct(Device $device) {
		$this->device = $device;
	}


	/**
	 * Running experiments on TOS1A in loop
	 * 
	 * @return App\Devices\TOS1A\Loop
	 */
	public function createTOS1ALoopDriver() {
		return new Loop($this->device);
	}

	
	/**
	 * Running experiments on TOS1A using Matlab
	 * 
	 * @return App\Devices\TOS1A\Matlab
	 */
	public function createTOS1AMatlabDriver() {
		return new Matlab($this->device);
	}

	/**
	 * Running experiments on TOS1A using Openmodelica
	 * 
	 * @return App\Devices\TOS1A\Openmodelica
	 */
	public function createTOS1AOpenmodelicaDriver() {
		return new Openmodelica($this->device);
	}

	
	/**
	 * Running experiments on TOS1A using Scilab
	 * 
	 * @return App\Devices\TOS1A\Scilab
	 */
	public function createTOS1AScilabDriver() {
		return new Scilab($this->device);
	}
}