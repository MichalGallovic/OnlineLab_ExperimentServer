<?php

namespace App\Classes\Transformers;

use League\Fractal\TransformerAbstract;

class GeneralArrayTransformer extends TransformerAbstract
{
	public function transform(array $data)
	{
		return $data;
	}
}