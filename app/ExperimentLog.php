<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExperimentLog extends Model
{
	public function experiment() {
		return $this->belongsTo(Experiment::class);
	}
}
