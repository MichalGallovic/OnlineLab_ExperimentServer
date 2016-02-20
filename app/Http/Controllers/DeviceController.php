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
use App\Classes\Repositories\DeviceDbRepository;
use App\Http\Requests\DeviceRunRequest;
use App\Http\Requests\DevDeviceRunRequest;

class DeviceController extends Controller
{
    use ApiRespondable;

    protected $deviceRepository;

    public function __construct(DeviceDbRepository $deviceRepo) {
        $this->deviceRepository = $deviceRepo;
    }

    public function statusAll(DeviceRequest $request) {
        $devices = $this->deviceRepository->getAll();
        $statuses = [];

        foreach ($devices as $device) {
            $statuses []= [
                "id"  =>  $device->id,
                "status"=>  $device->status
            ];
        }

        return $this->respondWithArray($statuses);
    }

    public function statusOne(DeviceRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        $deviceDriver = $device->driver();
        $status = $deviceDriver->status();
        
        return $this->respondWithArray([
                "status" => $status
            ]);
    }

    public function readOne(DeviceRequest $request, $id) {
    	try {
    		$device = $this->deviceRepository->getById($id);
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

    public function run(DevDeviceRunRequest $request, $id) 
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $type = strtolower($request->input('experiment_type'));
            $experimentType = ExperimentType::where('name', $type)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }


        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($experimentType->name);

        // $deviceDriver->run($request->input("experiment_input"), $request->input("requested_by"));
        
        // @TODO after dev fix it
        $deviceDriver->run($request->input("experiment_input"), 1);


        return $this->respondWithSuccess("Experiment ran successfully");
    }

    public function stop(DeviceRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        $deviceDriver = $device->driver();

        $deviceDriver->forceStop();

        $device->detachCurrentExperiment();
    
        // get current running experiment
        // make output with the use of it

        return $deviceDriver->read();
    }
}
