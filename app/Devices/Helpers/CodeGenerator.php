<?php

namespace App\Devices\Helpers;

use App\Experiment;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\File;

/**
* Code Generator for a new experiment
*/
class CodeGenerator
{
	protected $experiment;

	public function __construct(Experiment $experiment)
	{
		
		$this->experiment = $experiment;
	}

	public function generateCode()
	{
		$experiment = $this->experiment;
		$deviceName = Str::lower($experiment->device->type->name);
		$softwareName = Str::lower($experiment->software->name);

		$messages = new MessageBag();

		// Generating server_scripts folders
		$scriptsPath = base_path("server_scripts") . "/" . $deviceName . "/" . $softwareName;
		$this->createFolder($scriptsPath, $messages);

		// Generating config input/output files
		$configPath = config_path("devices") . "/" . $deviceName;
		$outputConfigContents = File::get(devices_path("Templates/OutputConfigTemplate.template"));
		$this->createFile($configPath . "/output.php", $messages, $outputConfigContents);
		$inputConfigContents = File::get(devices_path("Templates/InputConfigTemplate.template"));
		$this->createFile($configPath . "/" . $softwareName . "/input.php", $messages, $inputConfigContents);

		// Generating implementation classes
		$deviceName = Str::upper($deviceName);
		$softwareName = Str::ucfirst($softwareName);
		$deviceClassPath = devices_path($deviceName . "/" . $softwareName . ".php");
		$deviceClassContents = File::get(devices_path("Templates/DeviceDriverTemplate.template"));
		$deviceClassContents = str_replace("$1", $deviceName, $deviceClassContents);
		$deviceClassContents = str_replace("$2", $softwareName, $deviceClassContents);
		$this->createFile($deviceClassPath, $messages, $deviceClassContents);

		return $messages;
	}

	protected function createFile($path, $messages, $contents = "")
	{
		try {
			if(!File::exists($path)) {
				$this->createFolder(dirname($path), $messages);
			}
			if(!File::exists($path)) {
				File::put($path, $contents);
				$messages->add("new",$path);
			} else {
				throw new \ErrorException("File exists");
			}
		} catch(\ErrorException $e) {
			$messages->add("error",$e->getMessage() . " - " . $path);
		}
	}

	protected function createFolder($path, $messages)
	{
		try {
			File::makeDirectory($path, 0775, true);
			$messages->add("new",$path);
		} catch(\ErrorException $e) {
			$messages->add("error",$e->getMessage() . " - " . $path);
		}
	}
}