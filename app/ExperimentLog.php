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

	//@Todo check this method for empty result ?
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

	protected function parseOutput($contents) {
		$lines = array_filter(explode("\n", $contents));

		$lines = $this->skipHeader($lines);

		$output = [];

		foreach ($lines as $line) {
			$output []= array_map('floatval',explode(",", $line));
		}

		return $this->rotateOutput($output);
	}

	protected function skipHeader($lines) {
		// skip all lines, till csv  argument types
		$line = array_shift($lines);

		$outputArguments = $this->experiment->getOutputArguments();

		while($line != "===") {
			$line = array_shift($lines);
		}

		return $lines;
	}

	public function getInputik() {
		$input = $this->input_arguments;
		$input = json_decode($input);

		return array_keys(get_object_vars($input));
	}

	/**
	 * Rotate array
	 * from
	 * a1,b1,c1,d1 ...
	 * a2,b2,c2,d2 ...
	 * to
	 * a1,a2 ...
	 * b1,b2 ...
	 * c1,c2 ...
	 * d1,d2 ...
	 * @param  array $output
	 * @return array $rotatedOutput
	 */
	protected function rotateOutput($output) {
		$rotatedOutput = [];

		foreach ($output as $measurement) {
			foreach ($measurement as $index => $value) {
				$rotatedOutput[$index] []= (float) $value;
			}
		}

		return $rotatedOutput;
	}

}
