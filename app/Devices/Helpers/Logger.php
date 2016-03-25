<?php

namespace App\Devices\Helpers;

use App\Experiment;
use App\ExperimentLog;
use App\Devices\Scripts\Script;
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
	 * Command associated script
	 * @var App\Devices\Scripts\Script
	 */
	protected $script;

	/**
	 * Requested by
	 * @var int ID
	 */
	protected $requestedBy;

	public function __construct(Experiment $experiment, Script $script)
	{
		// $this->experimentLog = $experimentLog;
		$this->experiment = $experiment;
		$this->device = $experiment->device;
		$this->software = $experiment->software;
		$this->script = $script;
		$this->initDbLogging();
	}

	public function setMeasuringRate($rate)
	{
		$this->experimentLog->measuring_rate = $rate;
	}

	public function setSimulationTime($time)
	{
		$this->experimentLog->duration = $time;
	}

	public function setRequestedBy($userId)
	{
		$this->requestedBy = $userId;
	}

	protected function initDbLogging()
	{
		$logger = new ExperimentLog;
        $logger->experiment()->associate($this->experiment);
        $logger->input_arguments = json_encode($this->script->getInput());
        
        $logger->requested_by = $this->requestedBy;
        $logger->save();
        $this->device->currentExperiment()->associate($this->experiment)->save();
        $this->device->currentExperimentLogger()->associate($logger)->save();
        $this->experimentLogger = $logger;
	}

	public function createLogFile()
	{
		$this->createOutputFile($this->experimentLog->requested_by);
	}

	public function save()
	{
		$this->experimentLog->save();
	}

	/**
     * Generate path and create experiment 
     * log file with header contents
     * @param  int $id 	Id of a user, that requested the experiment
     */
    protected function createOutputFile($id)
    {
        $this->outputFile = $this->getLogsDirName() . "/" . $id . "_" . time() . ".log";
        $this->experimentLog->output_file = $this->outputFile;
        $header = $this->generateLogHeaderContents();

        if (!File::exists($this->outputFile)) {
            File::put($this->outputFile, $header);
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

        $path = storage_path() . "/logs/experiments/" . $deviceFolder . "/" . $softwareTypeFolder;
        
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
        $header .= $this->experimentLog->duration . "\n";
        $header .= $this->experimentLog->measuring_rate . "\n";
        $header .= $this->experimentLog->created_at . "\n";

        $input = $this->experimentLog->input_arguments;
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
}