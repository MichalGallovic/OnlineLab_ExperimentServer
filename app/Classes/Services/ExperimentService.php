<?php

namespace App\Classes\Services;

use App\Experiment;
use App\ExperimentLog;
use Illuminate\Support\Arr;
use App\Classes\WebServer\Server;
use App\Classes\Services\Exceptions\ExperimentCommandsNotDefined;

/**
* Experiment Service
*/
class ExperimentService
{
	
	protected $input;

	/**
	 * Requested Experiment
	 * @var App\Experiment
	 */
	protected $experiment;

	/**
	 * Succession of commands to execute
	 * in order to make an experiment
	 * @var array
	 */
	protected $commandsToExecute;

	/**
	 * Experiment log
	 * @var App\ExperimentLog
	 */
	protected $experimentLog;

	public function __construct($input, $deviceName, $softwareName)
	{
		$this->input = $input;
		$instanceName = Arr::get($input,"instance");
		$this->experiment = $this->getExperiment($deviceName, $softwareName, $instanceName);
		$this->device = $this->experiment->device;
		$this->commandsToExecute = $this->getExperimentCommands($deviceName, $softwareName);

		if(is_null($this->commandsToExecute) || !is_array($this->commandsToExecute) || empty($this->commandsToExecute)) {
			throw new ExperimentCommandsNotDefined($deviceName, $softwareName);
		}
	}

	protected function getExperiment($deviceName, $softwareName, $instanceName)
	{
		return Experiment::whereHas('device', function($query) use ($deviceName, $instanceName) {
				if($instanceName) {
					$query->where("name",$instanceName);
				}
				$query->whereHas('type', function($q) use ($deviceName) {
					$q->where('name', $deviceName);
				});
			})->whereHas('software', function($query) use ($softwareName) {
				$query->where('name', $softwareName);
			})->firstOrFail();
	}

	protected function getExperimentCommands($deviceName, $softwareName)
	{
		$configKeys = "experiments." . strtolower($deviceName) . "." . strtolower($softwareName);

		return config($configKeys);
	}

	public function run()
	{
		$results = [];
		foreach ($this->commandsToExecute as $commandName) {
			$input = $this->input;
			$input["input"] = Arr::get($input,"input.".$commandName);
			$input["command"] = $commandName;
			$command = new CommandService($input, $this->device->id);
			$results[$commandName] = $command->execute();
			if(is_null($this->experimentLog)) {
				$this->experimentLog = $command->getExperimentLog();
			}
		}
		return $results;
	}

	public function updateStatusWS($status)
	{
		$server = new Server(config("webserver.ip"));
		$server->updateExperimentStatus($this->experiment, "ready");
	}

	public function updateReportWs(ExperimentLog $log, $reportId)
	{
		$outputData = $log->readExperiment();
		$outputArguments = $log->experiment->getOutputArguments();
		$output = [];
		if(count(array_keys($outputData)) == count($outputArguments)) {
			$output = array_combine($outputArguments, $outputData);
		}

		$server = new Server(config("webserver.ip"));

		$server->updateExperimentReport($output, $reportId);
	}

    /**
     * Gets the Experiment log.
     *
     * @return App\ExperimentLog
     */
    public function getExperimentLog()
    {
        return $this->experimentLog;
    }
}