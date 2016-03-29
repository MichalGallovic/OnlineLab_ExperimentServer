<?php 

namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Commands\Command;
use App\Devices\Commands\ReadCommand;
use App\Devices\Commands\StopCommand;
use App\Devices\Commands\StartCommand;
use App\Devices\Exceptions\DeviceNotConnectedException;

abstract class AbstractTOS1A extends AbstractDevice
{

    protected $scriptPaths = [
        "read"    => "tos1a/read.py",
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

    protected function read(ReadCommand $command)
    {
    	$command->execute();
    }

    protected function start(StartCommand $command)
    {
        $command->execute();
        $command->wait();
    }

    protected function status(Command $command)
    {
    	$command->execute();
    }
}
