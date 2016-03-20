<?php 

namespace App\Devices\TOS1A;

use App\Devices\Traits\Outputable;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{
	use AsyncRunnable;

	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptNames["run"] = "matlab/run.py";
	}

	protected function getSimulationTime($input) {
		return $input["t_sim"];
	}

	protected function getMeasuringRate($input) {
		return $input["s_rate"];
	}
}