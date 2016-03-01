<?php

namespace App\Classes\Transformers;

use App\ExperimentLog;
use League\Fractal\TransformerAbstract;

class ExperimentDataTransformer extends TransformerAbstract
{

	public function transform(array $data)
	{
		return $data;
	}
}