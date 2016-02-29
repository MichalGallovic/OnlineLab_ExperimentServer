<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    public function device() {
    	return $this->belongsTo(Device::class);
    }

    public function type() {
    	return $this->belongsTo(ExperimentType::class,"experiment_type_id");
    }

    public function getInputArguments() {
    	
    }
}
