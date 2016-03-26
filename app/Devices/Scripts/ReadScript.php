<?php

namespace App\Devices\Scripts;

use App\Device;
use App\Devices\Scripts\Script;

/**
 * Read Script
 */
class ReadScript extends Script
{
	 /**
     * Device port
     * @var string
     */
    protected $port;

    /**
     * Script output
     * @var array
     */
    protected $output;

    public function __construct($path, Device $device)
    {
        parent::__construct($path, [], $device);
        $this->port = $device->port;
    }

    public function run()
    {        
        $arguments = $this->prepareArguments();
        $this->runProcess($this->path, $arguments);
        $this->output = $this->parseOutput($this->process->getOutput());
    }

    protected function prepareArguments()
    {
        return [
            $this->port
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

    /**
     * Gets the Script output.
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }
}