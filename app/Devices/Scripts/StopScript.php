<?php

namespace App\Devices\Scripts;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use App\Devices\Scripts\Script;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
* Stop script
*/
class StopScript extends Script
{


    public function __construct($path, Device $device)
    {
        parent::__construct($path, [], $device);
    }

    public function run()
    {        
        $arguments = $this->prepareArguments();
        $this->runProcess($this->path, $arguments);
        $this->cleanUp();
    }

    protected function prepareArguments($arguments = null)
    {
        return [
            $this->device->port
        ];
    }

    public function cleanUp()
    {
        $parentPids = json_decode($this->device->attached_pids);
        $allPids = [];
        foreach ($parentPids as $pid) {
            $allPids = array_merge($this->getAllChildProcesses($pid), $allPids);
        }

        $allPids = array_merge($parentPids, $allPids);

        // Kill all processes created by script
        foreach ($allPids as $pid) {
            $arguments = [
                "-TERM",
                $pid
            ];
            $builder = new ProcessBuilder();
            $builder->setPrefix("kill")->setArguments($arguments);
            $process = $builder->getProcess();
            $process->run();
        }
    }

     /**
     * Method uses pstree to get a tree of all
     * subprocesses created by a process
     * defined with PID
     *
     * It returns array with all processes created
     * for python+experiment runner and also
     * contains the pid of parent process
     * @return array
     */
    protected function getAllChildProcesses($pid)
    {
        $process = new Process("pstree -p ". $pid ." | grep -o '([0-9]\+)' | grep -o '[0-9]\+'");
         
        $process->run();
        $allProcesses = array_filter(explode("\n", $process->getOutput()));

        return $allProcesses;
    }
}
