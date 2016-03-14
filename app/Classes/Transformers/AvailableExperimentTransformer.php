<?php

namespace App\Classes\Transformers;

use App\Experiment;
use App\ExperimentLog;
use League\Fractal\TransformerAbstract;


class AvailableExperimentTransformer extends TransformerAbstract
{

	protected $availableIncludes = [
		"input_arguments",
		'output_arguments'
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
		return $this->item($experiment->getInputArguments(), new GeneralArrayTransformer);
	}

	public function includeOutputArguments(Experiment $experiment)
	{
		return $this->item($experiment->getOutputArguments(), new GeneralArrayTransformer);
	}
}