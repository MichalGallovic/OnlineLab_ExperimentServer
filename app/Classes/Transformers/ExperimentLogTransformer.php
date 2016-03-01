<?php

namespace App\Classes\Transformers;

use App\ExperimentLog;
use League\Fractal\TransformerAbstract;

class ExperimentLogTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		"measurements"
	];

	public function transform(ExperimentLog $log)
	{
		$experiment = $log->experiment;

		return [
			"id" => (int) $log->id,
			"device_type" => $experiment->device->type->name,
			"experiment_type" => $experiment->type->name,
			"duration" => $log->duration
		];
	}

	public function includeMeasurements(ExperimentLog $log) {
		$data = $log->reduceOutput();

		return $this->collection($data, new ExperimentDataTransformer);
	}
}