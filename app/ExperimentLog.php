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

	
	/**
	 * Reduce experiment output to values
	 * measured every x milliseconds
	 * @param  integer $everyMs
	 * @return array - reduced output
	 */
	public function reduceOutput($everyMs = null) {
		$output = $this->readExperiment();
		$duration = $this->duration;

		if(!isset($duration) || !isset($everyMs)) {
			return $output;
		}

		$outputMeasurements = count($output[0]);

		$wantMeasurements = $duration / ($everyMs/1000);


		if( $wantMeasurements > $outputMeasurements ) {
			return $output;
		}

		if( $wantMeasurements < 1) {
			return $output;
		}

		$every = floor($outputMeasurements / $wantMeasurements);

		$reducedOutput = [];

		foreach ($output as $index => $measurementsOfOneType) {
			for ($i = 0; $i < $outputMeasurements; $i+=$every) { 
				$reducedOutput[$index] [] = $measurementsOfOneType[$i];
			}
		}

		return $reducedOutput;
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

		$formattedOutput = [];

		foreach ($output as $measurement) {
			foreach ($measurement as $index => $value) {
				$formattedOutput[$index] []= (float) $value;
			}
		}

		return $formattedOutput;
	}

}
