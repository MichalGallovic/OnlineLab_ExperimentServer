<?php 

namespace App\Devices\TOS1A;

use App\Events\ProcessWasRan;
use App\Devices\Traits\Outputable;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractTOS1A implements DeviceDriverContract
{

	use Outputable, AsyncRunnable;

	public function __construct($device,$experimentType) {
		parent::__construct($device,$experimentType);

		$this->scriptNames["matlab"] = "matlab/run.py";
		
		// Definijng input arguments and rules
		// for validation
		$this->inputArguments = [
			"P" => "required",
			"I" => "required",
			"D" => "required",
			"c_fan" => "required",
			"c_lamp" => "required",
			"c_led" => "required",
			"in_sw" => "required",
			"out_sw" => "required",
			"t_sim" => "required",
			"s_rate" => "required",
			"input" => "required",
			"scifun" => "required"
		];
	}

	protected function getSimulationTime() {
		return $this->experimentInput["t_sim"];
	}

}