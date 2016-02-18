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
use App\Classes\Traits\ApiRespondable;

class DeviceController extends Controller
{

    use ApiRespondable;

    public function statusAll(DeviceRequest $request) {
        $devices = Device::all();
        $statuses = [];

        foreach ($devices as $device) {
            $statuses []= [
                "uuid"  =>  $device->uuid,
                "status"=>  $device->status
            ];
        }

        return $this->respondWithArray($statuses);
    }

    public function statusOne(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid',$uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        $deviceDriver = $device->driver();
        $status = $deviceDriver->status();
        
        return $this->respondWithArray([
                "status" => $status
            ]);
    }

    public function readOne(DeviceRequest $request, $uuid) {
    	try {
    		$device = Device::where('uuid',$uuid)->firstOrFail();
    	} catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
    	}

    	$deviceDriver = $device->driver();

    	return $deviceDriver->read();
    }

    public function readExperiment(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid',$uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        $deviceDriver = $device->driver();

        // return $deviceDriver->
    }

    public function run(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid', $uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        // Experiment Type & Input validation
        if(!$request->has('experiment_type')) {
            // Add some kind of error response
            return $this->errorWrongArgs("Experiment type not defined");
        }

        try {
            $type = strtolower($request->input('experiment_type'));
            $experimentType = ExperimentType::where('name', $type)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }

        if(!$request->has('experiment_input')) {
            // Add some kind of error response
            return $this->errorWrongArgs("Experiment arguments not specified");
        }



        // This could be moved inside Devices class
        $device->currentExperimentType()->associate($experimentType);
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
}
