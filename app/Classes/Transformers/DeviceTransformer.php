<?php

namespace App\Classes\Transformers;

use App\Device;
use App\ExperimentLog;
use League\Fractal\TransformerAbstract;
use App\Classes\Transformers\GeneralArrayTransformer;

class DeviceTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		"softwares"
	];

	public function transform(Device $device)
	{
		return [
			"id" => $device->id,
			"name" => $device->type->name,
			"status"	=>	$device->status(),
			"device_name"	=>	$device->name
		];
	}

	public function includeSoftwares(Device $device)
	{
		$experiments = $device->experiments;
		$available_experiments = [];

		foreach ($experiments as $experiment) {
			$available_experiments[]= [
				"id" 		=>	$experiment->id,
				"name"		=>	$experiment->software->name,
				"input"		=>	$experiment->getInputArguments(),
				"output"	=>	$experiment->getOutputArgumentsAll(),
				"commands"	=> 	$experiment->getImplementedCommands()
			];
		}

		return $this->item($available_experiments, new GeneralArrayTransformer);
	}
}