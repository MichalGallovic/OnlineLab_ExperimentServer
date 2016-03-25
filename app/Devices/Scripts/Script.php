<?php

namespace App\Devices\Scripts;

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
     * Process encapsulating the
     * running script
     * @var Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * Max execution time
     * @var int
     */
    protected $executionTime;

    /**
     * Timestamp script started
     * @var Carbon\Carbon
     */
    protected $startedAt;

    /**
     * Timestamp script ended
     * @var Carbon\Carbon
     */
    protected $endedAt;

    /**
     * Running script timed out
     * @var bool
     */
    protected $didTimeOut;

    public function __construct($path, $input)
    {
    	$this->checkPathCombinations($path);
    	$this->didTimeOut = false;
    	$this->input = $input;
    }

    // abstract protected function prepareArguments($arguments);
    abstract public function run();

    public function stop()
    {
    	$this->process->stop(0);
    }

    public function cleanUp()
    {
    	$pids = $this->getAllChildProcesses($this->process->getPid());

    	// Kill all processes created for experiment running
        foreach ($pids as $pid) {
            $arguments = [
                "-TERM",
                $pid
            ];
            $process = $this->runProcess("kill", $arguments);
        }
    }

    protected function runProcessAsync($arguments = [], $timeout = 20)
    {
    	$builder = new ProcessBuilder();
    	$builder->setPrefix($this->path);
    	$builder->setArguments($arguments);
    	
    	$process = $builder->getProcess();
    	$process->setTimeout($timeout);
    	$process->start();
    	$this->process = $process;
    }

    protected function runProcess($arguments = [])
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->path);
        $builder->setArguments($arguments);
        
        $process = $builder->getProcess();

        $process->run();
        $this->process = $process;
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

    /**
     * Sets the Path to script.
     *
     * @param string $path the path
     */
    protected function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Gets the Process encapsulating 
     * the running script
     *
     * @return Symfony\Component\Process\Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Get process pid
     * @return int
     */
    public function getPid()
    {
    	return $this->process->getPid();
    }

    /**
     * Sets the Max execution time.
     *
     * @param int $executionTime the execution time
     */
    protected function setExecutionTime($executionTime)
    {
        $this->executionTime = $executionTime;
    }

    /**
     * Did script timed out
     *
     * @return bool
     */
    public function timedOut()
    {
        return $this->didTimeOut;
    }
}
