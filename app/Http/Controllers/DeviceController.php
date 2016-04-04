<?php 

namespace App\Http\Controllers;

use App\Device;
use App\Software;
use App\ExperimentLog;
use App\Http\Requests;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use App\Events\ExperimentStarted;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\DeviceInitRequest;
use App\Http\Requests\DeviceStopRequest;
use App\Http\Requests\DeviceStartRequest;
use App\Http\Requests\DeviceChangeRequest;
use App\Http\Requests\DeviceCommandRequest;
use App\Http\Requests\DevDeviceStartRequest;
use App\Devices\Contracts\DeviceDriverContract;
use App\Http\Requests\DeviceExperimentsRequest;
use App\Classes\Repositories\DeviceDbRepository;
use App\Classes\Transformers\ReadDeviceTransformer;
use App\Devices\Exceptions\DeviceNotReadyException;
use App\Classes\Transformers\ExperimentLogTransformer;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Devices\Exceptions\DeviceNotConnectedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Devices\Exceptions\DeviceNotRunningExperimentException;
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;


class DeviceController extends ApiController
{
    protected $deviceRepository;

    protected $device;
    protected $experiment;

    public function __construct(Manager $fractal, DeviceDbRepository $deviceRepo)
    {
        parent::__construct($fractal);
        $this->deviceRepository = $deviceRepo;
    }

    public function executeCommand(DeviceCommandRequest $request, $id)
    {
        // Do we have this device in DB ?
        try {
            $this->device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        $software = Software::where('name', strtolower($request->input('software')))->first();
        
        $softwareName = !is_null($software) ? $software->name : null;        

        // Is command implemented on this experiment ? (device + software)
        $deviceDriver = $this->device->driver($softwareName);
        $command = $request->input('command');
        $deviceDriver->checkCommandSupport($command);

        $this->experiment = $this->device->getCurrentOrRequestedExperiment($softwareName);
        $this->experiment->validate($command, $request->input('input'));


        if(method_exists($this, $command)) {
            return $this->$command($deviceDriver, $request);
        }

        $commandMethod = strtolower($command) . "Command";

        if (App::environment() == 'local') {
            $output = $deviceDriver->$commandMethod($request->input("input"), 1);
        } else {
            $output = $deviceDriver->$commandMethod($request->input("input"), $request->input("requested_by"));
        }

        
        if($driver->commandSuccessful()) {
            return $this->respondWithSuccess("Command executed successfully");
        }

        return $this->respondWithError("Command execution ended with error!");
    }

    protected function read(DeviceDriverContract $driver, Request $request)
    {
        $output = $driver->readCommand();

        return $this->respondWithItem($this->device, new ReadDeviceTransformer($output));
    }

    protected function start(DeviceDriverContract $driver, Request $request)
    {
        // We don't want to run multiple experiments
        // at the same time, on once device
        if (!is_null($this->device->currentExperiment)) {
            throw new DeviceAlreadyRunningExperimentException;
        }

        if (App::environment() == 'local') {
            $driver->startCommand($request->input("input"), 1);
        } else {
            $$driver->startCommand($request->input("input"), $request->input("requested_by"));
        }

        $this->device = $this->device->fresh();

        $logger = $this->device->currentExperimentLogger;
        $result = is_null($logger) ? null : $logger->getResult();
        $this->device->detachCurrentExperiment();

        //@Todo set proper status codes
        if(is_null($result)) {
            return $this->setStatusCode(400)->respondWithError("Experiment was stopped!", 400);
        }

        return $this->respondWithSuccess($result);
    }

    protected function status(DeviceDriverContract $driver, Request $request)
    {
        $status = $driver->statusCommand();

        return $this->respondWithArray([
                "status" => $status
            ]);
    }

    protected function stop(DeviceDriverContract $driver, Request $request)
    {
        if (is_null($this->device->currentExperimentLogger)) {
            throw new DeviceNotRunningExperimentException;
        }

        $didStop = $driver->stopCommand();

        if (!$didStop) {
            return $this->errorInternalError("Experiment did not stop");
        }

        return $this->respondWithSuccess("Experiment stopped successfully");
    }

    public function statusAll(DeviceRequest $request)
    {
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
    public function readExperiment(DeviceRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $logger = $device->currentExperimentLogger;

        if (is_null($logger)) {
            return $this->errorForbidden("Experiment is not running. No data to read");
        }

        try {
            $output = $logger->readExperiment();
        } catch (FileNotFoundException $e) {
            return $this->errorInternalError("File not found or associated with experiment");
        }

        return $this->respondWithArray([
                "measuring_rate" =>  $logger->measuring_rate,
                "data" => $output
            ]);
    }

    public function previousExperiments(DeviceExperimentsRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $measurementsEvery = 200;

        if ($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

        $logs = $device->experimentLogs->sortByDesc("created_at");


        return $this->respondWithCollection($logs, new ExperimentLogTransformer($measurementsEvery));
    }

    public function latestExperimentOnDevice(DeviceExperimentsRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $measurementsEvery = 200;

        if ($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

        $log = $device->experimentLogs->sortByDesc('created_at')->first();


        return $this->respondWithItem($log, new ExperimentLogTransformer($measurementsEvery));
    }

}
