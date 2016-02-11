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
	protected $scriptsNames = [
		"readonce" => "readonce.py"
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


	public function __construct($device) {
		$this->device = $device;
		$this->scriptsPath = base_path() . "/server_scripts/TOS1A";
	}

	public function stop() {
		// run stop process + additional implementation
		// inside of each of concrete implementations
		// stop process that is running per matlab how ?
	}

	public function read() {

	}

	public function readOnce() {
		$path = $this->scriptsPath . "/" . $this->scriptsNames["readonce"];
		$arguments = [$this->device->port];

		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->run();

		event(new ProcessWasRan($process,$this->device));

		$output = $this->parseOutput($process->getOutput());
		
		return $this->makeResponse($output, $process);
	}

	protected function parseOutput($output) {
		$output = str_replace("$","", $output);
		$output = preg_replace("/\*(\w|\r|\n)*/", "", $output);

		$output = explode(",", $output);

		return $output;
	}

	protected function checkForStatus($output) {


	}

	protected function makeResponse($output, $process) {
		if(!$process->isSuccessful()) {
			$this->status = "offline";
		}

		if(count($this->outputArguments) != count($output)) {
			$this->status = "offline";
		} else {
			$output = array_combine($this->outputArguments, $output);

			if(floatval($output['f_temp_int']) == 0.00) {
				$this->status = "ready";
			} else {
				$this->status = "experiment";
			}
		}

		return [
			"device_uuid" => $this->device->uuid,
			"device_type" => $this->device->device_type,
			"experiment_type"   => $this->device->experiment_type,
			"status" => $this->status,
			"output" => array_filter($output)
		];
	}

	public function readExperiment() {
		// read contents of a file
	}

	public function status() {
		// vola read a parsuje to do array odpovede
		$output = $this->readOnce();


	}
}