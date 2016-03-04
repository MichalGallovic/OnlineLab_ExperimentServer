<?php

namespace App\Classes\Transformers;

use App\Device;
use App\ExperimentLog;
use League\Fractal\TransformerAbstract;

class DeviceTransformer extends TransformerAbstract
{
	public function transform(Device $device)
	{
		$experimentTypes = $device->experimentTypes;

		return [
			"id" => $device->id,
			"name" => $device->type->name,
			"experiment_types" => $experimentTypes->lists('name')->toArray()
		];
	}
}