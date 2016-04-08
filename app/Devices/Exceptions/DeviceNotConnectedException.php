<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DeviceNotConnectedException extends \Exception {
	
	/**
	 * Device name
	 * @var string
	 */
	protected $name;

	public function __construct($name)
	{
		$this->name = $name;
	}


	use ApiRespondable;



	public function getResponse() {
		return $this->errorInternalError("Device $this->name not connected");
	}
}