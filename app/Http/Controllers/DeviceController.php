<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\ApiController;
use App\Device;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Devices\Contracts\DeviceDriverContract;
use App\Software;
use App\Devices\Exceptions\DeviceNotConnectedException;
use App\Devices\Exceptions\DeviceNotReadyException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;
use App\Devices\Exceptions\ParametersInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Classes\Repositories\DeviceDbRepository;
use App\Http\Requests\DeviceRunRequest;
use App\Http\Requests\DevDeviceRunRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\ExperimentLog;
use App\Classes\Transformers\ExperimentLogTransformer;
use League\Fractal\Manager;
use App\Http\Requests\DeviceExperimentsRequest;
use Illuminate\Support\Facades\Artisan;

class DeviceController extends ApiController
{
    protected $deviceRepository;

    public function __construct(Manager $fractal, DeviceDbRepository $deviceRepo) {
        parent::__construct($fractal);
        $this->deviceRepository = $deviceRepo;
    }

    public function statusAll(DeviceRequest $request) {
        $devices = $this->deviceRepository->getAll();
        $statuses = [];

        // @Todo comamnd should will not be called on every
        // api request it will be scheduled with a cron job
        // but we could call it when requested with a 
        // specific api_token ? user permissions ? Guard?
        Artisan::call('server:devices:ping');

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
            return $this->deviceNotFound();
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
            return $this->deviceNotFound();
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
            return $this->deviceNotFound();
        }

        $logger = $device->currentExperimentLogger;

        if(is_null($logger)) {
            return $this->errorForbidden("Experiment is not running. No data to read");
        }

        try {
            $output = $logger->readExperiment();
        } catch(FileNotFoundException $e) {
            return $this->errorInternalError("File not found or associated with experiment");
        }

        return $this->respondWithArray([
                "measuring_rate" =>  $logger->measuring_rate,
                "data" => $output
            ]);
        
    }

    public function previousExperiments(DeviceExperimentsRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $measurementsEvery = 200;

        if($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

        $logs = $device->experimentLogs->sortByDesc("created_at");


        return $this->respondWithCollection($logs, new ExperimentLogTransformer($measurementsEvery));
    }

    public function latestExperimentOnDevice(DeviceExperimentsRequest $request, $id) {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $measurementsEvery = 200;

        if($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

        $log = $device->experimentLogs->sortByDesc('created_at')->first();


        return $this->respondWithItem($log, new ExperimentLogTransformer($measurementsEvery));
    }

    public function run(DevDeviceRunRequest $request, $id) 
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $softwareName = strtolower($request->input('software'));
            $software = Software::where('name', $softwareName)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }


        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($software->name);

        // This is for development
        if (App::environment() == 'local') {
            $experimentLog = $deviceDriver->run($request->input("input"), 1);
        } else {
            $experimentLog = $deviceDriver->run($request->input("input"), $request->input("requested_by"));
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
