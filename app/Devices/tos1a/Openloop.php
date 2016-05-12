<?php 

namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Scripts\Script;
use App\Devices\Scripts\ReadScript;
use App\Devices\Scripts\StopScript;
use Illuminate\Support\Facades\Log;
use App\Devices\Scripts\StartScript;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Scripts\TOS1A\Openloop\ChangeScript;

class Openloop extends AbstractDevice implements DeviceDriverContract
{

	protected $scriptPaths = [
		"start"		=>	"tos1a/openloop/start.py",
		"stop"		=>	"tos1a/stop.py",
		"read"		=>	"tos1a/read.py",
		"change"	=>	"tos1a/openloop/change.py"
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

	protected function change($input)
	{

		$script = new ChangeScript(
				$this->scriptPaths["change"],
				$input,
				$this->device
			);

		$script->run();
	}
}