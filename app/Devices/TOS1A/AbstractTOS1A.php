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

	protected $device;


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
		// read currently measured 
		$path = $this->scriptsPath . "/" . $this->scriptsNames["readonce"];
		$arguments = [$this->device->port];

		$builder = new ProcessBuilder();
		$builder->setPrefix($path);
		$builder->setArguments($arguments);
		
		$process = $builder->getProcess();
		$process->run();

		event(new ProcessWasRan($process,$this->device));

		// also check for exception - if failed write to database logs

		return $process->getOutput();
	}

	public function readExperiment() {
		// read contents of a file
	}

	public function status() {
		// vola read a parsuje to do array odpovede
	}
}