<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Device;
use App\Http\Requests\DeviceRequest;
use App\Devices\Contracts\DeviceDriverContract;

class DeviceController extends Controller
{

    public function statusAll() {

    }

    public function statusOne(DeviceRequest $request, $uuid) {
    	try {
    		$device = Device::where('uuid',$uuid)->firstOrFail();
    	} catch(ModelNotFoundException $e) {
    		// nieco
    	}

    	DeviceDriverContract $deviceDriver = $device->driver();

    	$status = $deviceDriver->readOnce();

    	return $status;

    	// checks if status has correct format
    	// json etc...

    	// return $status;
    }

    public function run() {

    }

    public function stop() {

    }
}
