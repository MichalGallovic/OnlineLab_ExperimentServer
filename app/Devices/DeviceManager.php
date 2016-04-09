<?php namespace App\Devices;

use App\Device;
use App\Experiment;
use Illuminate\Support\Str;
use App\Devices\TOS1A\Matlab;
use App\Devices\TOS1A\Scilab;
use App\Devices\TOS1A\OpenLoop;
use App\Devices\TOS1A\OpenModelica;
use App\Devices\Exceptions\DriverDoesNotExistException;

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
    public function __construct(Device $device, Experiment $experiment)
    {
        $this->device = $device;
        $this->experiment = $experiment;
    }


    public function createDriver($device, $software)
    {
    	$className = "App\Devices\\" .  Str::lower($device) . "\\" . Str::ucfirst($software);

    	if (!class_exists($className)) {
            throw new DriverDoesNotExistException;
        }

    	return new $className($this->device, $this->experiment);
    }
}
