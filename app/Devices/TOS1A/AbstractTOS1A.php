<?php 

namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Commands\StopCommand;
use App\Devices\Commands\StartCommand;
use App\Devices\Exceptions\DeviceNotConnectedException;

abstract class AbstractTOS1A extends AbstractDevice
{

    protected $scriptNames = [
        "read"    => "read.py",
        "stop"    => "tos1a/stop.py"
    ];

    public function __construct($device, $experiment)
    {
        parent::__construct($device, $experiment);
        
    }

    protected function stop(StopCommand $command)
    {
    	$command->execute();
    }

    protected function start(StartCommand $command)
    {
        $command->execute();
        $command->wait();
    }

    public function isConnected()
    {
        $output = $this->getDeviceOutput();
        
        if (empty($output)) {
            return false;
        }

        // array_combine returns false, when number of 
        // keys and values does not match, in our case
        // that is when something went wrong
        return is_array($output);
    }

    public function isReady()
    {
        // device TOS1A responds with zero filtered internal temperature
        // if is connected and not running experiment
        return $this->isConnected() && floatval($this->output["f_temp_int"]) == 0.0;
    }

    public function isRunningExperiment()
    {
        // device TOS1A responds with non zero filtered internal temperature
        // when running experiment
        return $this->isAlreadyExperimenting() || $this->isStartingExperiment();
    }

    /**
     * Helper functions
     */

    protected function isStartingExperiment()
    {
        // When device is ready and pid is already attached
        // it means the experiment is initializing
        // (i.e. starting matlab takes some time)
        return $this->isReady() && !is_null($this->device->attached_pids);
    }

    protected function isAlreadyExperimenting()
    {
        return ($this->isConnected($this->output) &&
            floatval($this->output["f_temp_int"]) != 0.0);
    }
}
