<?php 

namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Scripts\Script;
use App\Devices\Scripts\ReadScript;
use App\Devices\Scripts\StopScript;
use App\Devices\Scripts\StartScript;
use App\Devices\Contracts\DeviceDriverContract;

class Openloop extends AbstractDevice implements DeviceDriverContract
{

	protected $scriptPaths = [
		"start"	=>	"tos1a/openloop/start.py",
		"stop"	=>	"tos1a/stop.py",
		"read"	=>	"tos1a/read.py"
	];

	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
	}

	protected function start($input)
	{
		$script = new StartScript(
			$this->scriptPaths["start"],
			$input,
			$this->device,
			$this->experimentLog->output_path
			);

		$script->run();

	}

	protected function stop($input)
	{
		$script = new StopScript(
				$this->scriptPaths["stop"],
				$this->device
			);

		$script->run();
	}

	protected function read($input)
	{
		$script = new ReadScript(
				$this->scriptPaths["read"],
				$this->device
			);

		$script->run();

		return $script->getOutput();
	}

	protected function init($input)
	{
		return "Output z init commandu";
	}

	protected function change($input)
	{
		$startExperiment = [1,2,3,4];
		return $startExperiment;
	}

	// These methods have to be implemented
	// only if you are implementing
	// START command
	protected function parseDuration($input)
	{
		return $input["t_sim"];
	}

	protected function parseSamplingRate($input)
	{
		return $input["s_rate"];
	}
	
}