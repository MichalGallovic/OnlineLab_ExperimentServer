<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class ExperimentLog extends Model
{
	public function experiment() {
		return $this->belongsTo(Experiment::class);
	}

	public function getResult() {
		if(!is_null($this->finished_at)) {
			return "Experiment was successful!";
		} else if(!is_null($this->stopped_at)) {
			return "Experiment was stopped!";
		} else if(!is_null($this->timedout_at)) {
			return "Experiment was timed out!";
		}

	}

	public function readExperiment() {
		$contents = File::get($this->output_path);
		$output = $this->parseOutput($contents);

		return $output;
	}

	protected function getInputArguments() {
		$deviceName = strtolower($this->experiment->device->type->name);
		$experimentType = strtolower($this->experiment->type->name);
		$configPath = "devices." . $deviceName . ".output";
		

		return config($configPath);
	}

	protected function parseOutput($contents) {
		$output = str_replace("\r","",$contents);
		$output = array_filter(explode("\n", $output));
		
		$output = array_map(function($line) {
			$line = substr($line, 1, strpos($line, "*") - 1);
			$arr = explode(",",$line);
			return $arr;
		}, $output);

		return $output;
	}

}
