<?php 

namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{
	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptPaths["start"] = "tos1a/matlab/start.py";
	}
}