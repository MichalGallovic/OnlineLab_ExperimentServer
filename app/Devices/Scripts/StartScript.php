<?php

namespace App\Devices\Scripts;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use App\Devices\Scripts\Script;

/**
* Start script
*/
class StartScript extends Script
{
    /**
     * Path to output file
     * @var string
     */
    protected $outputFile;


    public function __construct($path, $input, Device $device, $outputFile)
    {
        parent::__construct($path, $input, $device);
        $this->outputFile = $outputFile;
    }

    public function run()
    {        
        $arguments = $this->prepareArguments($this->input);
        $this->runProcess($this->path, $arguments);
    }


    protected function prepareArguments($arguments)
    {
        $input = "";

        foreach ($arguments as $key => $value) {
            $input .= $key . ":" . $value . ",";
        }
        $input = substr($input, 0, strlen($input) - 1);

        return [
            "--port=" . $this->device->port,
            "--output=" . $this->outputFile,
            "--input=" . $input
        ];
    }
}
