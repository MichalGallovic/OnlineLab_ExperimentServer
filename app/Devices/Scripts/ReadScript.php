<?php

namespace App\Devices\Scripts;

use App\Device;
use App\Experiment;
use App\Devices\Scripts\Script;

/**
 * Read Script
 */
class ReadScript extends Script
{
    /**
     * Script output
     * @var array
     */
    protected $output;

    public function __construct($path, Device $device)
    {
        parent::__construct($path, [], $device);
    }

    public function run()
    {        
        $arguments = $this->prepareArguments();
        $this->runProcess($this->path, $arguments);
        $this->output = $this->parseOutput($this->process->getOutput());
    }

    public function getOutput()
    {
        return $this->output;
    }

    protected function prepareArguments($arguments = null)
    {
        return [
            $this->device->port
        ];
    }

    /**
     * Parses raw string output from device, into array of floats
     * @param  string $output Raw
     * @return array          
     */
    protected function parseOutput($output)
    {
        $output = array_map('floatval', explode(',', $output));
        return $output;
    }
}