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
		$this->scriptNames["start"] = "matlab/start.py";
	}

	protected function getSimulationTime($input) {
		return $input["t_sim"];
	}

	protected function getMeasuringRate($input) {
		return $input["s_rate"];
	}
}