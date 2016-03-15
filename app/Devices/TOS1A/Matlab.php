<?php 

namespace App\Devices\TOS1A;

use App\Devices\Traits\Outputable;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{
	use Outputable, AsyncRunnable;

	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
		$this->scriptNames["matlab"] = "matlab/run.py";
	}

	protected function getSimulationTime() {
		return $this->experimentInput["t_sim"];
	}

	protected function getMeasuringRate() {
		return $this->experimentInput["s_rate"];
	}
}