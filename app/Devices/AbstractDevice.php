<?php

namespace App\Devices;

use App\Device;
use Carbon\Carbon;
use App\Experiment;
use App\Events\ProcessWasRan;
use App\Events\ExperimentStarted;
use App\Devices\Traits\Outputable;
use App\Events\ExperimentFinished;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\ProcessBuilder;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Devices\Exceptions\DeviceNotConnectedException;
use App\Devices\Exceptions\DeviceNotRunningExperimentException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;

abstract class AbstractDevice
{
    /**
     * Path to folder with device specific scripts
     * @var string
     */
    protected $scriptsPath;

    /**
     * Paths to read/stop/run scripts relative to
     * $scriptsPath
     * @var array
     */
    protected $scriptNames;
    
    /**
     * Path to experiment output file
     * @var string
     */
    protected $outputFile;

    /**
     * Experiment output
     * @method getDeviceOutput
     * @var array
     */
    protected $output = null;

    /**
     * Experiment status
     * @method getDeviceStatus
     * @var array
     */
    protected $status;

    /**
     * Device model (from DB)
     * @var App\Device
     */
    protected $device;

    /**
     * Experiment model (from DB)
     * @var App\Experiment
     */
    protected $experiment;

    /**
     * Experiment user input
     * @method getSimulationTime
     * @method getMeasuringRate
     * @var array
     */
    protected $experimentInput;

    /**
     * ExperimentLog model (from DB)
     * Keeps track of current experiment information
     * i.e. path to output file, input, duration, measuring rate...
     * @var App\ExperimentLog
     */
    protected $experimentLogger;

    /**
     * Experiment Running time
     * maximum time, the program will wait till experiment will end
     * @var int
     */
    protected $experimentRunningTime;
    
    /**
     * Unix timestamp of last time the output from device
     * was retrieved
     * @var float
     */
    protected $outputRetrieved;
    

    /**
     * Max time the experiment initializes itself
     */
    const MAX_INITIALIZATION_TIME = 25;

    public function __construct(Device $device, Experiment $experiment)
    {
        $this->device = $device;
        $this->experiment = $experiment;
        $this->scriptsPath = $this->generateScriptsPath();
    }

    /**
     * Abstract methods to implement - also check 
     * App\Devices\Contracts\DeviceDriverContract
     * to see public interface that has to be implemented
     */

    /**
     * Get simulation time has to be implemented
     * per experiment basis, because simulation
     * time is deduced from the input arguments
     * 
     * @return int
     */
    abstract protected function getSimulationTime($input);

    /**
     * Get measuring rate (usually equals to the sampling)
     * time - number which tells how often results are
     * measured
     * @return int
     */
    abstract protected function getMeasuringRate($input);

    /**
     * Based on device statuses types that are defined in
     * App\Devices\Contracts\DeviceDriverContract
     * child class has to implement status
     * getters
     */
    abstract public function isConnected();

    abstract public function isReady();

    abstract public function isRunningExperiment();

    /**
     * Device Reading
     */

    /**
     * Read output in 2 step
     * 
     * 1. get output from physical device
     * 2. query device / check output and deduce device status
     * 
     * @todo Some SW environments as matlab can crash
     * when read during experiment, so we could check before ?
     * @return array
     */
    public function read()
    {
        $this->getDeviceOutput();
        $this->getDeviceStatus();
        return $this->output;
    }

    public function getDeviceOutput()
    {
        // Lazily instantiante the output
        // if it was not obtained, get it
        // upon first request or if the value was
        // retrieved before more than 200ms
        $now = microtime(true)*1000;
        $diffRetrieved = $now - $this->outputRetrieved;

        if (is_null($this->output)  || ($diffRetrieved > 100)) {
            $output = $this->readOnce();
            $this->combimeOutputWithArguments($output, $this->experiment->getOutputArguments());
        }

        return $this->output;
    }

    /**
     * Read physical device output
     * outputRetrieved marks last time 
     * physical device was queried
     */
    protected function readOnce()
    {
        $path = $this->getScriptPath("read");

        $arguments = [$this->device->port];

        $process = $this->runProcess($path, $arguments);
        $this->outputRetrieved = microtime(true)*1000;

        $output = $this->parseOutput($process->getOutput());

        return $output;
    }


    /**
     * Tries to combine output array with arguments array
     * assigns it to the $this->output var
     * @param  array $output    Parsed device output
     * @param  array $arguments Device output arguments
     */
    protected function combimeOutputWithArguments($output, $arguments)
    {
        try {
            $this->output = array_combine($arguments, $output);
        } catch (\Exception $e) {
            $this->output = null;
        }
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
     * Read and deduce device status
     * Very similar to @method read
     * @return string
     */
    public function status()
    {
        $this->getDeviceOutput();
        $this->getDeviceStatus();
        return $this->status;
    }

    public function getDeviceStatus()
    {
        if ($this->isRunningExperiment()) {
            $this->status = DeviceDriverContract::STATUS_EXPERIMENTING;
        } elseif ($this->isReady()) {
            $this->status = DeviceDriverContract::STATUS_READY;
            // When device is ready, we don't necesarilly
            // need to sent the output, but it could
            // be set on again just, by commenting
            // this out
            $this->output = null;
        } else {
            $this->status = DeviceDriverContract::STATUS_OFFLINE;
        }
    }

    public function run($input)
    {
        $this->experimentInput = $input;
        $this->experimentLogger = $this->device->currentExperimentLogger;
        $this->generateOutputFilePath($this->experimentLogger->requested_by);
        $this->experimentLogger->output_path = $this->outputFile;
        $this->experimentLogger->measuring_rate = $this->getMeasuringRate($this->experimentInput);
        $this->experimentLogger->save();
    }

    public function stop()
    {
        // Stops experiment and cleans up all processes
        if (!is_null($this->device->attached_pids)) {
            $this->stopExperimentRunner();
        }
        // Stop the experiment on the physical device
        $this->stopDevice();
        // Detaches the main process pid from db
        $this->detachPids();

        if ($this->isLoggingExperiment() &&
            !$this->wasForceStopped() &&
            !$this->wasTimedOut()) {
            event(new ExperimentFinished($this->device));
        }

        $this->device->detachCurrentExperiment();

        return $this->experimentLogger->fresh();
    }

    public function forceStop()
    {
        if (is_null($this->device->currentExperimentLogger)) {
            throw new DeviceNotRunningExperimentException;
        }

        if ($this->isLoggingExperiment()) {
            $logger = $this->device->currentExperimentLogger;
            $logger->stopped_at = Carbon::now();
            $logger->save();
        }
        
        // Stops experiment and cleans up all processes
        if (!is_null($this->device->attached_pids)) {
            $this->stopExperimentRunner();
        }
        // Stop the experiment on the physical device
        $this->stopDevice();
        // Detaches the main process pid from db
        $this->detachPids();

        $this->device->status = DeviceDriverContract::STATUS_READY;
        $this->device->save();
    }

    public function wasForceStopped()
    {
        if (is_null($this->device->currentExperimentLogger)) {
            return true;
        }
        
        return !is_null($this->device->currentExperimentLogger->stopped_at);
    }

    public function wasTimedOut()
    {
        return !is_null($this->experimentLogger->fresh()->timedout_at);
    }

    public function stopDevice()
    {
        $path = $this->getScriptPath("stop");
        $arguments = [$this->device->port];

        $process = $this->runProcess($path, $arguments);
    }

    protected function attachPid($pid)
    {
        $this->device->fresh();
        $pids = json_decode($this->device->attached_pids);
        $pids []= $pid;
        $this->device->attached_pids = json_encode($pids);
        $this->device->save();
    }

    protected function detachPids()
    {
        $this->device->attached_pids = null;
        $this->device->save();
    }

    protected function isLoggingExperiment()
    {
        $this->device = $this->device->fresh();
        return !is_null($this->device->currentExperimentLogger);
    }

    protected function stopExperimentRunner()
    {
        $this->device = $this->device->fresh();
        $attached_pids = json_decode($this->device->attached_pids);
        $pids = [];
        foreach ((array)$attached_pids as $pid) {
            $pids = array_merge($this->getAllChildProcesses($pid), $pids);
        }

        // Kill all processes created for experiment running
        foreach ($pids as $pid) {
            $arguments = [
                "-TERM",
                $pid
            ];
            $process = $this->runProcessWithoutLog("kill", $arguments);
        }
    }
    

    

    protected function runProcess($path, $arguments = [])
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($path);
        $builder->setArguments($arguments);
        
        $process = $builder->getProcess();
        $process->run();

        event(new ProcessWasRan($process, $this->device));

        return $process;
    }

    // This method is temporary and only called where
    // some error occur in calling processes
    // i.e. killing children processes
    // when forcing experiment to 
    // stop
    // Such occasion producesses lots of errors
    // but works - this could be fixed someday :D
    protected function runProcessWithoutLog($path, $arguments = [])
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($path);
        $builder->setArguments($arguments);
        
        $process = $builder->getProcess();
        $process->run();

        return $process;
    }

    protected function runProcessAsync($path, $arguments = [], $timeout = 20)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($path);
        $builder->setArguments($arguments);
        
        $process = $builder->getProcess();
        $process->setTimeout($timeout);
        $process->start();

        return $process;
    }

    protected function waitOrTimeoutAsync($process, $time)
    {
        $started = time();
        $experimentTimedOut = false;

        while ($process->isRunning()) {
            // check some stuff with timeout
            $now = time();

            if ($now - $started > $time) {
                $experimentTimedOut = true;
                break;
            }
            
            usleep(1000000);
        }

        event(new ProcessWasRan($process, $this->device));

        if ($experimentTimedOut) {
            $this->experimentLogger->timedout_at = Carbon::now();
            $this->experimentLogger->save();
        }
    }

    protected function runExperiment($arguments)
    {
        $this->experimentRunningTime = $this->prepareExperiment($arguments);
        $arguments = $this->prepareArguments($arguments);
        $process = $this->runProcess(
            $this->getScriptPath("run"),
            $arguments,
            $this->experimentRunningTime
            );
        return $process;
    }

    protected function runExperimentAsync($arguments)
    {
        $this->experimentRunningTime = $this->prepareExperiment($arguments);
        $arguments = $this->prepareArguments($arguments);
        $process = $this->runProcessAsync(
             $this->getScriptPath("run"),
             $arguments,
             $this->experimentRunningTime
             );

        $this->attachPid($process->getPid());
        return $process;
    }

    protected function prepareExperiment($arguments)
    {
        $duration = $this->getSimulationTime($this->experimentInput);
        $this->experimentLogger->duration = $duration;
        $this->experimentLogger->save();

        // Max simulation time is just a rough estimate
        // it is in place to check whether the
        // experiment is not running longer
        // than expected

        return $duration + self::MAX_INITIALIZATION_TIME;
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
     * Generate path to concrete script
     * @param  string $name Script name
     * @return string       Path to script
     */
    protected function getScriptPath($name)
    {
        return $this->scriptsPath . "/" . $this->scriptNames[$name];
    }

    /**
     * Output file helper methods 
     */

    /**
     * Generate path to logs directory of specific experiment
     * i.e. root/storage/logs/experiments/tos1a/matlab/
     * If the folder does not yet exist, it creates it
     * @return string Path to logs directory
     */
    protected function getLogsDirName()
    {
        $namespaceSegments = explode("\\", get_called_class());   
        $softwareTypeFolder = end($namespaceSegments);
        $deviceFolder = $namespaceSegments[count($namespaceSegments) - 2];

        $path = storage_path() . "/logs/experiments/" . strtolower($deviceFolder) . "/" . strtolower($softwareTypeFolder);
        
        if(!File::exists($path)) {
        	File::makeDirectory($path, 0775, true);
        }

        return $path;
    }
    
    /**
     * Generate path and create experiment 
     * log file with header contents
     * @param  int $id 	Id of a user, that requested the experiment
     */
    protected function generateOutputFilePath($id)
    {
        $this->outputFile = $this->getLogsDirName() . "/" . $id . "_" . time() . ".log";
        
        $header = $this->generateLogHeaderContents();

        if(!File::exists($this->outputFile)) {
        	File::put($this->outputFile, $header);
        }
    }

    /**
     * Generate header of a log file
     * @return string Header contents
     */
    protected function generateLogHeaderContents() {
    	$header = $this->device->type->name . "\n";
    	$header .= $this->experiment->software->name . "\n";
    	$header .= $this->getSimulationTime($this->experimentInput) . "\n";
    	$header .= $this->getMeasuringRate($this->experimentInput) . "\n";
    	$header .= $this->experimentLogger->created_at . "\n";

    	$input = $this->experimentLogger->input_arguments;
    	$input = json_decode($input);
    	$inputNames = collect(array_keys(get_object_vars($input)))->__toString();
    	$inputValues = collect(array_values(get_object_vars($input)))->__toString();
    	$inputNames = str_replace("[","",$inputNames);
    	$inputNames = str_replace("]","",$inputNames);

    	$inputValues = str_replace("[","",$inputValues);
    	$inputValues = str_replace("]","",$inputValues);

    	$header .= $inputNames . "\n";
    	$header .= $inputValues. "\n";

    	$names = $this->experiment->getOutputArguments();
		$names = collect($names);
		$names = $names->__toString();
		$names = str_replace("[","",$names);
		$names = str_replace("]","",$names);
    	
    	$header .= $names . "\n";
    	$header .= "===\n";

    	return $header;
    }
    
    /**
     * Generate path to base folder with read/run/stop/other scripts
     * i.e. app/server_scripts/tos1a (tos1a is deduced from the
     * concrete class that is instantiated when specific
     * experiment = device+software is requested)
     * @return string Path to device scripts
     */
    protected function generateScriptsPath()
    {
        $namespaceSegments = explode("\\", get_called_class());

        $deviceName = $namespaceSegments[count($namespaceSegments) - 2];

        return base_path() . "/server_scripts/" . strtolower($deviceName);
    }
}
