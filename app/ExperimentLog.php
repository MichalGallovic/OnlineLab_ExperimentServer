<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
