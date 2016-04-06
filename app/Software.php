<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
	protected $fillable = ["name"];
	
    public function devices() {
    	return $this->belongsToMany(Device::class,"experiments");
    }
}
