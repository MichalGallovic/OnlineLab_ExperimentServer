<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{

    public function devices() {
    	return $this->belongsToMany(Device::class,"experiments");
    }
}
