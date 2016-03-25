<?php

namespace App\Devices\Scripts;

use App\Devices\Scripts\Script;

/**
* Start script
*/
class StartScript extends Script
{

    /**
     * Expected script running time
     * @var int
     */
    protected $runningTime;

    public function __construct($path, $input)
    {
        parent::__construct($path, $input);
    }

    public function run()
    {
        // get running time
        
        $arguments = $this->prepareArguments($this->input);

        $this->runProcessAsync(
            $this->path,
            $arguments,
            30
        );
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
