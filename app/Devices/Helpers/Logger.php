<?php

namespace App\Devices\Helpers;

use App\Experiment;
use App\ExperimentLog;
use App\Events\ProcessWasRan;
use App\Events\ExperimentFinished;
use App\Devices\Scripts\StartScript;
use Illuminate\Support\Facades\File;

class Logger
{
	/**
	 * Experiment log (DB)
	 * @var App\ExperimentLog
	 */
	protected $experimentLogger;


	/**
	 * Path to experiment log file
	 * @var string
	 */
	protected $outputFilePath;

	/**
	 * Experiment reference (DB)
	 * @var App\Experiment
	 */
	protected $experiment;

	/**
	 * Device reference (DB)
	 * @var App\Device
	 */
	protected $device;

	/**
	 * Software reference (DB)
	 * @var App\Software
	 */
	protected $software;

	/**
	 * Requested by
	 * @var int ID
	 */
	protected $requestedBy;


	public function __construct(Experiment $experiment, $input, $requestedBy = null)
	{
		$this->experiment = $experiment;
		$this->device = $experiment->device;
		$this->software = $experiment->software;
		$this->requestedBy = $requestedBy;
		$this->initDbLogging($input);
	}

	public function setMeasuringRate($rate)
	{
		$this->experimentLogger->measuring_rate = $rate;
	}

	public function setSimulationTime($time)
	{
		$this->experimentLogger->duration = $time;
	}

	protected function initDbLogging($input)
	{
		$logger = new ExperimentLog;
        $logger->experiment()->associate($this->experiment);
        $logger->input_arguments = json_encode($input);
        
        $logger->requested_by = $this->requestedBy;
        $logger->save();
        $this->device->currentExperiment()->associate($this->experiment)->save();
        $this->device->currentExperimentLogger()->associate($logger)->save();
        $this->experimentLogger = $logger;
	}

	public function createLogFile()
	{
		$this->createOutputFile($this->experimentLogger->requested_by);
		$this->experimentLogger->save();
	}

	public function save()
	{
		$this->experimentLogger->save();
	}

	public function saveScript(StartScript $script)
	{
		event(new ProcessWasRan($script->getProcess(), $this->device));

        if ($script->timedOut()) {
            $this->experimentLogger->timedout_at = Carbon::now();
            $this->experimentLogger->save();
        } else {
        	event(new ExperimentFinished($this->device));
        }
	}

	/**
     * Generate path and create experiment 
     * log file with header contents
     * @param  int $id 	Id of a user, that requested the experiment
     */
    protected function createOutputFile()
    {
        $this->outputFilePath = $this->getOutputFilePath();
        $this->experimentLogger->output_path = $this->outputFilePath;
        $header = $this->generateLogHeaderContents();

        if (!File::exists($this->outputFilePath)) {
            File::put($this->outputFilePath, $header);
        }
    }

    /**
     * Generate path to logs directory of specific experiment
     * i.e. root/storage/logs/experiments/tos1a/matlab/
     * If the folder does not yet exist, it creates it
     * @return string Path to logs directory
     */
    protected function getLogsDirName()
    {
        $deviceTypeFolder = strtolower($this->device->type->name);
        $softwareTypeFolder = strtolower($this->software->name);

        $path = storage_path() . "/logs/experiments/" . $deviceTypeFolder . "/" . $softwareTypeFolder;
        
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
        }

        return $path;
    }

    /**
     * Generate header of a log file
     * @return string Header contents
     */
    protected function generateLogHeaderContents()
    {
        $header = $this->device->type->name . "\n";
        $header .= $this->software->name . "\n";
        $header .= $this->experimentLogger->duration . "\n";
        $header .= $this->experimentLogger->measuring_rate . "\n";
        $header .= $this->experimentLogger->created_at . "\n";

        $input = $this->experimentLogger->input_arguments;
        $input = json_decode($input);
        $inputNames = collect(array_keys(get_object_vars($input)))->__toString();
        $inputValues = collect(array_values(get_object_vars($input)))->__toString();
        $inputNames = str_replace("[", "", $inputNames);
        $inputNames = str_replace("]", "", $inputNames);

        $inputValues = str_replace("[", "", $inputValues);
        $inputValues = str_replace("]", "", $inputValues);

        $header .= $inputNames . "\n";
        $header .= $inputValues. "\n";

        $names = $this->experiment->getOutputArguments();
        $names = collect($names);
        $names = $names->__toString();
        $names = str_replace("[", "", $names);
        $names = str_replace("]", "", $names);
        
        $header .= $names . "\n";
        $header .= "===\n";

        return $header;
    }


    /**
     * Gets the Experiment log (DB).
     *
     * @return App\ExperimentLog
     */
    public function getExperimentLogger()
    {
        return $this->experimentLogger;
    }

    /**
     * Gets the Path to experiment log file.
     *
     * @return string
     */
    public function getOutputFilePath()
    {
    	if($this->outputFilePath) {
    		return $this->outputFilePath;
    	}

    	$this->outputFilePath = $this->getLogsDirName() . "/" . $this->requestedBy . "_" . time() . ".log";

        return $this->outputFilePath;
    }
}