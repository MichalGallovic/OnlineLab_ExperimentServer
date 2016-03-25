<?php

namespace App\Devices\Traits;

use App\Devices\Commands\StartCommand;

trait AsyncRunnable
{

    protected function start(StartCommand $command)
    {
        $command->execute();
        $command->wait();
    }
}
