<?php

namespace App\Devices\Traits;

trait AsyncRunnable
{

    protected function start($input)
    {
        $experimentProcess =  $this->startExperimentAsync($input);
        $this->waitOrTimeoutAsync($experimentProcess, $this->experimentRunningTime);
    }
}
