<?php

namespace App\Classes\Transformers;

use App\ExperimentLog;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Input;

class ExperimentLogTransformer extends TransformerAbstract
{
	protected $measuremenetsEveryMs;
	protected $duration;

	public function __construct($measuremenetsEveryMs)
	{
		$this->measuremenetsEveryMs = intval($measuremenetsEveryMs);
	}

	protected $availableIncludes = [
		"measurements",
		"input_arguments",
		'output_arguments'
	];



	public function transform(ExperimentLog $log)
	{
		$experiment = $log->experiment;
		$this->duration = $log->duration;

		return [
			"id" => (int) $log->id,
			"device" => $experiment->device->type->name,
			"software" => $experiment->software->name,
			"duration" => $this->duration,
			"started_at"	=>	$log->created_at->diffForHumans()
		];
	}

	public function includeMeasurements(ExperimentLog $log) {
		$data = [];

		$everyMs = 0;

		if($this->isRequestedRateValid($log)) {
			$data = $log->reduceOutput($this->measuremenetsEveryMs);
			$everyMs = $this->measuremenetsEveryMs;
		} else {
			$data = $log->reduceOutput();
			$everyMs = $log->measuring_rate;
		}

		$data = $log->reduceOutput($this->measuremenetsEveryMs);

		return $this->item($data, new ExperimentDataTransformer($everyMs));
	}

	/**
	 * Is requested measuring rate slower, than the rate 
	 * experiment was measured at?
	 * Is requested measuring rate smaller than the
	 * experiment duration?
	 * @return boolean
	 */
	private function isRequestedRateValid(ExperimentLog $log) {
		return $this->measuremenetsEveryMs > $log->measuring_rate && 
		$this->measuremenetsEveryMs/1000 < $this->duration;
	}

	public function includeInputArguments(ExperimentLog $log)
	{
		return $this->item($log->experiment->getInputArguments(), new GeneralArrayTransformer);
	}

	public function includeOutputArguments(ExperimentLog $log)
	{
		return $this->item($log->experiment->getOutputArguments(), new GeneralArrayTransformer);
	}
}