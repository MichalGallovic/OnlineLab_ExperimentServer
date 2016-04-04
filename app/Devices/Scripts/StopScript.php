<?php

namespace App\Devices\Scripts;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use App\Devices\Scripts\Script;

/**
* Stop script
*/
class StopScript extends Script
{

    /**
     * Expected script running time
     * @var int
     */
    protected $runningTime;

      /**
     * Device port
     * @var string
     */
    protected $port;

    public function __construct($path, Experiment $experiment)
    {
        parent::__construct($path, [], $experiment);
        $this->port = $this->device->port;
    }

    public function run()
    {        
        $arguments = $this->prepareArguments();
        $this->runProcess($this->path, $arguments);
    }

    protected function prepareArguments()
    {
        return [
            $this->port
        ];
    }
}
