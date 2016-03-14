<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;
use Illuminate\Support\Str;
use App\Device;

class DefaultExperimentNotFoundException extends \Exception {
	
	use ApiRespondable;

	protected $device;

	public function __construct(Device $device)
	{
		$this->device = $device;
	}

	public function getResponse() {
		$message = 'Default experiment for  ' . $device->type->name . " not found!";
		return $this->errorInternalError($message);
	}
}