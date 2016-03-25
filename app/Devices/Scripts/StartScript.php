<?php

namespace App\Devices\Scripts;

use Carbon\Carbon;
use App\Devices\Scripts\Script;

/**
* Start script
*/
class StartScript extends Script
{

    /**
     * Device port
     * @var string
     */
    protected $port;

    /**
     * Path to output file
     * @var string
     */
    protected $outputFile;

    /**
     * Expected script running time
     * @var int
     */
    protected $runningTime;

    public function __construct($path, $port, $outputFile, $input)
    {
        parent::__construct($path, $input);
        $this->port = $port;
        $this->outputFile = $outputFile;
    }

    public function run()
    {        
        $arguments = $this->prepareArguments($this->input);

        $this->runProcessAsync($arguments,$this->executionTime);
        $this->startedAt = Carbon::now();
    }

    public function waitOrTimeout()
    {
        $started = time();

        while ($this->process->isRunning()) {
            $now = time();

            if ($now - $started > $this->executionTime) {
                $this->didTimeOut = true;
                break;
            }
            
            usleep(1000000);
        }

        $this->endedAt = Carbon::now();
    }

    protected function prepareArguments($arguments)
    {
        $input = "";

        foreach ($arguments as $key => $value) {
            $input .= $key . ":" . $value . ",";
        }
        $input = substr($input, 0, strlen($input) - 1);

        return [
            "--port=" . $this->port,
            "--output=" . $this->outputFile,
            "--input=" . $input
        ];
    }
}
