<?php

namespace App\Devices\Helpers;

use App\Device;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\File;

/**
* Code Generator for a new experiment
*/
class CodeGenerator
{
	protected $device;


	public function __construct(Device $device)
	{
		
		$this->device = $device;
	}

	public function generateCode()
	{

		$softwares = $this->device->softwares;
		$messageBag = new MessageBag();

		$this->createFolders($softwares, $messageBag);
		$this->createFiles($softwares, $messageBag);

		return $messageBag;
	}

	protected function createFolders($softwares, MessageBag $messageBag)
	{
		$deviceName = Str::lower($this->device->type->name);

		// Generating server_scripts folders
		$deviceScriptPath = base_path("server_scripts") . "/" . $deviceName;
		$this->createFolder($deviceScriptPath, $messageBag);

		// Generating config folders
		$deviceConfigPath = config_path("devices") . "/" . $deviceName;
		$this->createFolder($deviceConfigPath, $messageBag);

		// Generating implementation folders
		$deviceImplPath = devices_path($deviceName);
		$this->createFolder($deviceImplPath, $messageBag);

		foreach ($softwares as $software) {
			$softwareScriptPath = $deviceScriptPath . "/" . Str::lower($software->name);
			$this->createFolder($softwareScriptPath, $messageBag);

			$softwareConfigPath = $deviceConfigPath . "/" . Str::lower($software->name);
			$this->createFolder($softwareConfigPath, $messageBag);
		}

	}
	
	protected function createFiles($softwares, MessageBag $messageBag)
	{
		$deviceName = Str::lower($this->device->type->name);	

		$deviceConfigPath = config_path("devices") . "/" . $deviceName;
		$outputConfigContents = File::get(devices_path("Templates/OutputConfigTemplate.template"));
		$this->createFile($deviceConfigPath . "/output.php", $messageBag, $outputConfigContents);
		$inputConfigContents = File::get(devices_path("Templates/InputConfigTemplate.template"));
		
		$deviceImplPath = devices_path($deviceName);
		$deviceImplContents = File::get(devices_path("Templates/DeviceDriverTemplate.template"));

		foreach ($softwares as $software) {
			$this->createFile($deviceConfigPath . "/" . Str::lower($software->name) . "/input.php", $messageBag, $inputConfigContents);

			$softwareImplPath = $deviceImplPath . "/" . Str::ucfirst($software->name) . ".php";
			$softwareImplContents = str_replace("$1", $deviceName, $deviceImplContents);
			$softwareImplContents = str_replace("$2", Str::ucfirst($software->name), $softwareImplContents);
			$this->createFile($softwareImplPath, $messageBag, $softwareImplContents);
		}
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
			File::makeDirectory($path, 0766);
			$messages->add("new",$path);
		} catch(\ErrorException $e) {
			$messages->add("error",$e->getMessage() . " - " . $path);
		}
	}
}