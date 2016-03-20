<?php 

namespace App\Devices\TOS1A;

use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class OpenLoop extends AbstractTOS1A implements DeviceDriverContract
{
	use AsyncRunnable;

	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptNames["run"] = "openloop/run.py";
	}

	protected function getSimulationTime($input) {
		return $input["t_sim"];
	}

	protected function getMeasuringRate($input) {
		return $input["s_rate"];
	}
}