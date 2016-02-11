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

    public function readOne(DeviceRequest $request, $uuid) {
    	try {
    		$device = Device::where('uuid',$uuid)->firstOrFail();
    	} catch(ModelNotFoundException $e) {
    		// nieco
    	}

    	$deviceDriver = $device->driver();

    	return $deviceDriver->read();
    	// checks if status has correct format
    	// json etc...

    	// return $status;
    }

    public function run(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid', $uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {

        }

        $deviceDriver = $device->driver();

        return $deviceDriver->run();
    }

    public function stop(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid', $uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {

        }

        $deviceDriver = $device->driver();

        $deviceDriver->stop();
        
        return $deviceDriver->read();
    }
}
