<?php

namespace App\Classes\Transformers;

use App\Device;
use League\Fractal\TransformerAbstract;

class ReadDeviceTransformer extends TransformerAbstract
{
	
	protected $deviceOutput;

	public function __construct($deviceOutput)
	{
		$this->deviceOutput = $deviceOutput;
	}

	public function transform(Device $device)
	{
		return [
			"device" => $device->type->name,
			"software"   => $device->currentSoftwareName(),
			"output" => $this->deviceOutput
		];
	}
}