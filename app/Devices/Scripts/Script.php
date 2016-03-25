<?php

namespace App\Devices\Scripts;

use App\Device;
use App\Software;
use Illuminate\Support\Facades\File;
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

    public function __construct($path, $input)
    {
    	$this->checkPathCombinations($path);

    	$this->input = $input;
    }

    abstract protected function prepareArguments($arguments);
    abstract public function run();

    protected function runProcessAsync($path, $arguments = [], $timeout = 20)
    {
    	$builder = new ProcessBuilder();
    	$builder->setPrefix($path);
    	$builder->setArguments($arguments);
    	
    	$process = $builder->getProcess();
    	$process->setTimeout($timeout);
    	$process->start();

    	$this->process;
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
}
