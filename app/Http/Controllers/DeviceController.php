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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\ExperimentLog;

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

    /**
     * Read experiment directly from file
     * and respond with http response
     * not websocket
     *
     * This method was created for development purposes
     * so developer can easily debug on the appserver
     * 
     * @param  DeviceRequest $request [description]
     * @param  mixed $id (int)
     * @return mixed json
     */
    public function readExperiment(DeviceRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        $logger = ExperimentLog::first();

        // if(is_null($logger)) {
        //     return $this->errorForbidden("Experiment is not running. Not data to read");
        // }

        try {
            $output = $logger->readExperiment();
        } catch(FileNotFoundException $e) {
            return $this->errorInternalError("File not found or associated with experiment");
        }

        return $this->respondWithArray([
                "data" => $output
            ]);
        
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

        // This is for development
        if (App::environment() == 'local') {
            $experimentLog = $deviceDriver->run($request->input("experiment_input"), 1);
        } else {
            $experimentLog = $deviceDriver->run($request->input("experiment_input"), $request->input("requested_by"));
        }

        return $this->respondWithSuccess($experimentLog->getResult());
    }

    public function stop(DeviceRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        $deviceDriver = $device->driver();

        $deviceDriver->forceStop();

        if(!$deviceDriver->wasForceStopped()) {
            return $this->errorInternalError("Experiment did not stop");
        }

        return $this->respondWithSuccess("Experiment stopped successfully");
    }
}
