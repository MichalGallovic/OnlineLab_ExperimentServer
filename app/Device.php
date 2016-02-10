<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public function driver() {
    	$deviceManager = new DeviceManager;

    	$method = 'create' . Str::camel($this->type) . 'Device';

    	if(!method_exists($dev, $method)) {
    		// Throw exception
    	}

    	return $deviceManager->$method();
    }
}
