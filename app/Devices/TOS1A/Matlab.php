<?php 

namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Scripts\ReadScript;
use App\Devices\Scripts\StopScript;
use App\Devices\Scripts\StartScript;
use App\Devices\Contracts\DeviceDriverContract;

class Matlab extends AbstractDevice implements DeviceDriverContract
{
	protected $scriptPaths = [
		"start"	=>	"tos1a/matlab/start.py",
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