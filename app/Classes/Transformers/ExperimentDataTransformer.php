<?php

namespace App\Classes\Transformers;

use App\ExperimentLog;
use League\Fractal\TransformerAbstract;

class ExperimentDataTransformer extends TransformerAbstract
{
	protected $measurementsEveryMs;

	public function __construct($measurementsEveryMs)
	{
		$this->measurementsEveryMs = $measurementsEveryMs;
	}




	public function transform(array $data)
	{

		return [
			"measurements_rate"	=>	$this->measurementsEveryMs,
			"measurements" 		=> 	$data
		];
	}
}