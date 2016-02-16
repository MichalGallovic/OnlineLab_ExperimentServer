<?php namespace App\Devices\Exceptions;

class ExperimentTimedOutException extends \Exception {

	public function getResponse() {
		return response()->json([
				"error" => "Experiment timed out (took longer than expected)"
			],400);
	}
}