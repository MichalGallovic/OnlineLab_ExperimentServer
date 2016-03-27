<?php 

namespace App\Devices\TOS1A;

use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{
	use AsyncRunnable;

	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptNames["start"] = "tos1a/matlab/start.py";
	}
}