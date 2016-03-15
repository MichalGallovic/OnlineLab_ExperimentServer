<?php namespace App\Devices;

use App\Devices\TOS1A\OpenLoop;
use App\Devices\TOS1A\OpenModelica;
use App\Devices\TOS1A\Scilab;
use App\Devices\TOS1A\Matlab;
use App\Device;
use App\Experiment;

/**
 * Manages the instatiation strategy
 * of experiment 
 */
class DeviceManager
{
	protected $device;
	protected $experiment;

	/**
	 * @param App\Device
	 */
	public function __construct(Device $device, Experiment $experiment) {
		$this->device = $device;
		$this->experiment = $experiment;
	}


	/**
	 * Running experiments on TOS1A in openloop
	 * 
	 * @return App\Devices\TOS1A\Loop
	 */
	public function createTOS1AOpenloopDriver() {
		return new OpenLoop($this->device, $this->experiment);
	}

	
	/**
	 * Running experiments on TOS1A using Matlab
	 * 
	 * @return App\Devices\TOS1A\Matlab
	 */
	public function createTOS1AMatlabDriver() {
		return new Matlab($this->device, $this->experiment);
	}

	/**
	 * Running experiments on TOS1A using Openmodelica
	 * 
	 * @return App\Devices\TOS1A\Openmodelica
 */
	public function createTOS1AOpenmodelicaDriver() {
		return new OpenModelica($this->device, $this->experiment);
	}

	
	/**
	 * Running experiments on TOS1A using Scilab
	 * 
	 * @return App\Devices\TOS1A\Scilab
	 */
	public function createTOS1AScilabDriver() {
		return new Scilab($this->device, $this->experiment);
	}
}