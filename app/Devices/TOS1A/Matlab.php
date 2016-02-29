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
		$this->inputArguments = config("devices.tos1a.experiments.matlab.input");
	}

	protected function getSimulationTime() {
		return $this->experimentInput["t_sim"];
	}

}