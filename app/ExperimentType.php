<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExperimentType extends Model
{
    public function devices() {
    	return $this->hasMany(Device::class);
    }
}
