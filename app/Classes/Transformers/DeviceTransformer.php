<?php

namespace App\Classes\Transformers;

use App\Device;
use App\ExperimentLog;
use League\Fractal\TransformerAbstract;

class DeviceTransformer extends TransformerAbstract
{
	public function transform(Device $device)
	{
		$experiments = $device->experiments;

		$available_experiments = [];

		foreach ($experiments as $experiment) {
			$available_experiments[]= [
				"id" 	=>	$experiment->id,
				"name"	=>	$experiment->type->name,
				"input"	=>	$experiment->getInputArguments(),
				"output"=>	$experiment->getOutputArguments()
			];
		}

		return [
			"id" => $device->id,
			"name" => $device->type->name,
			"experiments" => $available_experiments
		];
	}
}