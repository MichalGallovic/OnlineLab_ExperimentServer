<?php 

namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\ExperimentCommandNotAvailable;

class Openloop extends AbstractTOS1A implements DeviceDriverContract
{
	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptNames["start"] = "tos1a/openloop/start.py";
	}
	
}