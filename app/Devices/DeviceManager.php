<?php namespace App\Devices;

use App\Devices\TOS1A\OpenLoop;
use App\Devices\TOS1A\OpenModelica;
use App\Devices\TOS1A\Scilab;
use App\Devices\TOS1A\Matlab;
use App\Device;
use App\ExperimentType;

/**
 * Manages the instatiation strategy
 * of experiment 
 */
class DeviceManager
{
	protected $device;
	protected $experimentType;

	/**
	 * @param App\Device
	 */
	public function __construct(Device $device, ExperimentType $experimentType) {
		$this->device = $device;
		$this->experimentType = $experimentType;
	}


	/**
	 * Running experiments on TOS1A in loop
	 * 
	 * @return App\Devices\TOS1A\Loop
	 */
	public function createTOS1AOpenloopDriver() {
		return new OpenLoop($this->device, $this->experimentType);
	}

	
	/**
	 * Running experiments on TOS1A using Matlab
	 * 
	 * @return App\Devices\TOS1A\Matlab
	 */
	public function createTOS1AMatlabDriver() {
		return new Matlab($this->device, $this->experimentType);
	}

	/**
	 * Running experiments on TOS1A using Openmodelica
	 * 
	 * @return App\Devices\TOS1A\Openmodelica
	 */
	public function createTOS1AOpenmodelicaDriver() {
		return new OpenModelica($this->device, $this->experimentType);
	}

	
	/**
	 * Running experiments on TOS1A using Scilab
	 * 
	 * @return App\Devices\TOS1A\Scilab
	 */
	public function createTOS1AScilabDriver() {
		return new Scilab($this->device, $this->experimentType);
	}
}