<?php 

namespace App\Devices\TOS1A;

use App\Events\ProcessWasRan;
use App\Devices\Traits\Outputable;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class OpenLoop extends AbstractTOS1A implements DeviceDriverContract
{
	use Outputable, AsyncRunnable;

	public function __construct($device,$experimentType) {
		parent::__construct($device,$experimentType);
		$this->scriptNames["openloop"] = "run.py";
		
		// Definijng input arguments and rules
		// for validation
		$this->inputArguments = [
			"c_fan" => "required",
			"c_lamp" => "required",
			"c_led" => "required",
			"t_sim" => "required",
			"s_rate" => "required"
		];
	}

	protected function getSimulationTime() {
		return $this->experimentInput["t_sim"];
	}
}