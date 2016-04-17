<?php

namespace App\Classes\Services;

use App\Experiment;
use Illuminate\Support\Arr;
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
	public function __construct($input, $deviceName, $softwareName)
	{
		$this->input = $input;
		$this->experiment = $this->getExperiment($deviceName, $softwareName);
		$this->device = $this->experiment->device;
		$this->commands = $this->getExperimentCommands($deviceName, $softwareName);

		if(is_null($this->commands) || !is_array($this->commands) || empty($this->commands)) {
			throw new ExperimentCommandsNotDefined($deviceName, $softwareName);
		}
	}

	protected function getExperiment($deviceName, $softwareName)
	{
		return Experiment::whereHas('device', function($query) use ($deviceName) {
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
		foreach ($this->commands as $commandName) {
			$input = $this->input;
			$input["input"] = Arr::get($input,"input.".$commandName);
			$input["command"] = $commandName;
			$command = new CommandService($input, $this->device->id);
			$results[$commandName] = $command->execute();
		}
		return $results;
	}
}