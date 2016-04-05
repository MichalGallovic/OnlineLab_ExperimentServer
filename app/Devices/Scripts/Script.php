<?php

namespace App\Devices\Scripts;

use App\Device;
use App\Experiment;
use App\Events\ProcessWasRan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use App\Devices\Scripts\Exceptions\ScriptDoesNotExistException;

/**
* Base Script representation
*/
abstract class Script
{
    /**
     * Path to script
     * @var string
     */
    protected $path;

    /**
     * Script input arguments
     * @var array
     */
    protected $input;

    /**
     * Device the script is concerned with
     * @var App\Device
     */
    protected $device;

    /**
     * Process encapsulating the
     * running script
     * @var Symfony\Component\Process\Process
     */
    protected $process;


    public function __construct($path, $input, Device $device)
    {
        // Checks if script exists on path relative to server_scripts
        // or at the absolute path (if neither, throws exception)
    	$this->checkPathCombinations($path);
    	$this->input = $input;
    	$this->device = $device;
    }

    abstract public function run();
    abstract protected function prepareArguments($arguments);


    protected function runProcess($path, $arguments = [], $timeout = 30)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($path);
        $builder->setArguments($arguments);
        
        $process = $builder->getProcess();
        $process->setTimeout($timeout);
        $process->start();
        $this->process = $process;
        $this->device->attachPid($process->getPid());
        $this->device->save();

        while($process->isRunning()) {
            // waiting for process to finish
        }

    }

    public function basePath()
    {
    	return base_path("server_scripts");
    }

    protected function checkPathCombinations($path)
    {
    	$absolutePath = $this->basePath() . "/" . $path;

    	if(File::exists($absolutePath)) {
    		$this->path = $absolutePath;
    	} else if(File::exists($path)) {
    		$this->path = $path;
    	} else {
    		throw new ScriptDoesNotExistException([$absolutePath, $path]);
    	}
    }

    

    /**
     * Gets the Script input arguments.
     *
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Gets the Path to script.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    protected function logProcess(Process $process)
    {
    	event(new ProcessWasRan($process, $this->device));
    }

  
}
