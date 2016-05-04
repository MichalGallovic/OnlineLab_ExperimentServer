<?php

namespace App\Devices\Scripts\tos1a\scilab;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use App\Devices\Scripts\StartScript;

/**
* Start script
*/

class StartScriptScilab extends StartScript
{

    public function run()
    {        
        $arguments = $this->prepareArguments($this->input);
        $this->runProcess($this->path, $arguments, 60);
    }
    
}
