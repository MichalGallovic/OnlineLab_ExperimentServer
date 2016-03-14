<?php

namespace App\Devices\Traits;

trait Outputable {

	protected $outputDir;

	protected $outputFile;

	protected function generateOutputFileNameWithId($id) {
		$this->makeDirname();
		$this->outputFile = $id . "_" . time() . ".log";
	}

	protected function makeDirname() {
		//@Todo add checks if the folder exists ?
		$namespaceSegments = explode("\\",get_class());
		
		$softwareTypeFolder = end($namespaceSegments);
		$deviceFolder = $namespaceSegments[count($namespaceSegments) - 2];

		$this->outputDir = storage_path() . "/logs/experiments/" . strtolower($deviceFolder) . "/" . strtolower($softwareTypeFolder);
	}

	protected function prepareArguments($arguments) {
		$input = "";

		foreach ($arguments as $key => $value) {
			$input .= $key . ":" . $value . ",";
		}
		$input = substr($input, 0, strlen($input) - 1);

		return [
			"--port=" . $this->device->port,
			"--output=" . $this->getOutputFilePath(),
			"--input=" . $input
		];
	}

	protected function getOutputFilePath() {
		return $this->outputDir . "/" . $this->outputFile;
	}

}