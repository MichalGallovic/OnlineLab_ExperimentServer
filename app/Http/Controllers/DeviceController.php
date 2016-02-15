<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Device;
use App\Http\Requests\DeviceRequest;
use App\Devices\Contracts\DeviceDriverContract;
use App\ExperimentType;
use App\Devices\Exceptions\DeviceNotConnectedException;
use App\Devices\Exceptions\DeviceNotReadyException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;
use App\Devices\Exceptions\ParametersInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeviceController extends Controller
{

    public function statusAll(DeviceRequest $request) {
        $devices = Device::all();
        $statuses = [];

        foreach ($devices as $device) {
            $statuses []= [
                "uuid"  =>  $device->uuid,
                "status"=>  $device->status
            ];
        }

        return response()->json($statuses);
    }

    public function statusOne(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid',$uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        $deviceDriver = $device->driver();
        $status = $deviceDriver->status();
        
        return response()->json([
                "status" => $status
            ]);
    }

    public function readOne(DeviceRequest $request, $uuid) {
    	try {
    		$device = Device::where('uuid',$uuid)->firstOrFail();
    	} catch(ModelNotFoundException $e) {
            return $this->errorNotFound();
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
            return $this->errorNotFound();
        }

        // Experiment Type & Input validation
        if(!$request->has('experiment_type')) {
            // Add some kind of error response
            return response()->json([
                    "error" => "Experiment type not defined"
                ], 400);
        }

        try {
            $type = strtolower($request->input('experiment_type'));
            $experiment_type = ExperimentType::where('name', $type)->first();
        } catch(ModelNotFoundException $e) {
            // Return sth
        }

        if(!$request->has('experiment_input')) {
            // Add some kind of error response
            return response()->json([
                    "error" => "Experiment arguments not specified"
                ],400);
        }



        // This could be moved inside Devices class
        $device->experiment_type_id = $experiment_type->id;
        $device->save();

        // When everything looks fine it is
        // time to boot up classes for
        // 
        try {
            $deviceDriver = $device->driver();
        } catch(DeviceNotConnectedException $e) {
            return $e->getResponse();
        }

        try {
            $deviceDriver->run($request->input('experiment_input'));
        } catch(DeviceAlreadyRunningExperimentException $e) {
            return $e->getResponse();
        } catch(ParametersInvalidException $e) {
            return $e->getResponse();
        }

        return $deviceDriver->read();
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

    protected function errorNotFound() {
        return response()->json([
                "error" => "Device not found"
            ],400);
    }
}
