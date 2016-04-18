<?php

namespace App\Classes\Transformers;

use App\Experiment;
use App\ExperimentLog;
use League\Fractal\TransformerAbstract;


class AvailableExperimentTransformer extends TransformerAbstract
{

	protected $availableIncludes = [
		"input_arguments",
		'output_arguments',
		"experiment_commands"
	];

	public function transform(Experiment $experiment)
	{
		return [
			"id"	=>	$experiment->id,
			"device" 		=>	$experiment->device->type->name,
			"software"	=>	$experiment->software->name
		];
	}

	public function includeInputArguments(Experiment $experiment)
	{
		$inputArguments = $experiment->getInputArguments(); 
		$inputArguments = is_null($inputArguments) ? [] : $inputArguments;
		return $this->item($inputArguments, new GeneralArrayTransformer);
	}

	public function includeOutputArguments(Experiment $experiment)
	{
		return $this->item($experiment->getOutputArgumentsAll(), new GeneralArrayTransformer);
	}

	public function includeExperimentCommands(Experiment $experiment)
	{
		$commands = $experiment->getExperimentCommands();
		$commands = !is_null($commands) ? $commands : [];
		return $this->item($commands, new GeneralArrayTransformer);
	}
}