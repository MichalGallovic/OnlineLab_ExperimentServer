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
            // Return sth
        }

        if(!$request->has('experiment_type')) {
            // Add some kind of error response
            return redirect()->back();
        }

        try {
            $experiment_type = ExperimentType::where('name', $request->input('experiment_type'))->first();
        } catch(ModelNotFoundException $e) {
            // Return sth
        }

        if(!$request->has('input')) {
            // Add some kind of error response
            return redirect()->back();
        }

        $device->experiment_type_id = $experiment_type->id;
        $device->save();

        $deviceDriver = $device->driver();

        return $deviceDriver->run($request->input('input'));
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
