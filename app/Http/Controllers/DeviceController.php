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

    public function run(DeviceRunRequest $request, $id) 
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


        // create experiment log
        // associate experiment with this log
        // send id to server API


        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($experimentType->name);

        // opravit aby ked nieco bezi iny clovek nemohol prepisat v tabulke co bezi
        $device->currentExperimentType()->associate($experimentType)->save();

        // $this->experimentRepository->create($experimentType, $device, $request->input("experiment_input"));

        $deviceDriver->run($request->input("experiment_input"));

        $device->detachCurrentExperiment();

        return $this->respondWithSuccess("Experiment ran successfully");
    }

    public function stop(DeviceRequest $request, $uuid) {
        try {
            $device = Device::where('uuid', $uuid)->firstOrFail();
        } catch(ModelNotFoundException $e) {

        }

        $deviceDriver = $device->driver();

        $deviceDriver->stop();

        $device->detachCurrentExperiment();
    
        // get current running experiment
        // make output with the use of it

        return $deviceDriver->read();
    }
}
