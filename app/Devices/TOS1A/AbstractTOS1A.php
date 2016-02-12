<?php namespace App\Devices\TOS1A;

use App\Devices\Contracts\DeviceDriverContract;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Events\ProcessWasRan;

abstract class AbstractTOS1A
{
	// These @vars could be inside config file
	// and initialized in constructor

	protected $scriptsPath;
	protected $scriptNames = [
		"readonce" => "readonce.py",
		"stop"     => "stop.py"
	];

	protected $outputArguments = [
		"temp_chip",
		"f_temp_int",
		"d_temp_ext",
		"f_temp_ext",
		"d_temp_ext",
		"f_light_int_lin",
		"d_light_int_lin",
		"f_light_int_log",
		"d_light_int_log",
		"I_bulb",
		"V_bulb",
		"I_fan",
		"V_fan",
		"f_rpm",
		"d_rmp"
	];

	protected $device;
	protected $status;
	protected $output;
	protected $process;
	protected $runtime;

	public function __construct($device) {
		$this->device = $device;
		$this->scriptsPath = base_path() . "/server_scripts/TOS1A";
		$this->output = null;
	}

	public function stop() {
		// run stop process + additional implementation
		// inside of each of concrete implementations
		// stop process that is running per matlab how ?

		$this->stopDevice();
	}

	public function read() {
		return $this->readOnce();
	}

	public function readOnce() {
		$path = $this->getScriptPath("readonce");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);

		event(new ProcessWasRan($process,$this->device));

		$output = $this->parseOutput($process->getOutput());
		
		$this->createStatusAndOutput($output, $process);

		return $this->makeResponse($output, $process);
	}

	protected function stopDevice() {
		$path = $this->getScriptPath("stop");
		$arguments = [$this->device->port];

		$process = $this->runProcess($path, $arguments);

		event(new ProcessWasRan($process, $this->device));
	}

	protected function getScriptPath($name) {
		return $this->scriptsPath . "/" . $this->scriptNames[$name];
	}

	protected function runProcess($path, $arguments = []) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->run();

		return $process;
	}

	protected function runProcessAsync($path, $arguments = [], $timeout = 20) {
		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->setTimeout($timeout);
		$process->start();

		return $process;
	}

	protected function runProcessForceAsync($path, $arguments = []) {
		// $builder = new ProcessBuilder();
		// $builder->setPrefix($path);

		// // $arguments []= "> /dev/null";
		// // $arguments []= "2> /dev/null";
		// // $arguments []= "&";

		// $builder->setArguments($arguments);
		
		// $process = $builder->getProcess();
		$process = new Process($path . " > /dev/null 2> /dev/null &");
		$process->run();

		return $process;
	}

	protected function parseOutput($output) {
		$output = str_replace("$","", $output);
		$output = preg_replace("/\*(\w|\r|\n)*/", "", $output);

		$output = explode(",", $output);

		return $output;
	}

	protected function makeResponse($output, $process) {
		return [
			"device_uuid" => $this->device->uuid,
			"device_type" => $this->device->device_type,
			"experiment_type"   => $this->device->experiment->name,
			"status" => $this->status,
			"output" => $this->output
		];
	}

	protected function createStatusAndOutput($output, $process) {
		$this->device->fresh();

		if(!$process->isSuccessful()) {
			$this->status = "offline";
		}

		if(count($this->outputArguments) != count($output)) {
			$this->status = "offline";
		} else {
			$output = array_combine($this->outputArguments, $output);

			if(floatval($output['f_temp_int']) == 0.00) {
				if($this->device->status == "initializing_experiment") {
					$this->status = "initializing_experiment";
					return;
				}
				$this->status = "ready";
			} else {
				$this->status = "experimenting";
				$this->output = $output;
			}
		}
	}

	public function readExperiment() {
		// read contents of a file
	}

	public function status() {
		// vola read a parsuje to do array odpovede
		$output = $this->readOnce();


	}
}