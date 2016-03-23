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
use App\Devices\Exceptions\DeviceAlreadyRunningExperimentException;


class DeviceController extends ApiController
{
    protected $deviceRepository;

    public function __construct(Manager $fractal, DeviceDbRepository $deviceRepo)
    {
        parent::__construct($fractal);
        $this->deviceRepository = $deviceRepo;
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

    public function statusOne(DeviceRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $device->getStatus();
        return $this->respondWithArray([
                "status" => $device->status
            ]);
    }

    public function readOne(DeviceRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->deviceNotFound();
        }

        $deviceDriver = $device->driver();

        $output = $deviceDriver->read();

        return $this->respondWithItem($device, new ReadDeviceTransformer($output));
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

    public function change(DeviceChangeRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $softwareName = strtolower($request->input('software'));
            $software = Software::where('name', $softwareName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }

        $experiment = $device->getCurrentOrRequestedExperiment($software->name);

        $experiment->validateChange($request->input('input'));

        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($software->name);

        $deviceDriver->changeCommand($request->input("input"));
    }

    public function listCommands(Request $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $softwareName = strtolower($request->input('software'));
            $software = Software::where('name', $softwareName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }

        $experiment = $device->getCurrentOrRequestedExperiment($software->name);

        $deviceDriver = $device->driver($software->name);

        return $this->respondWithArray([
                "commands" => $deviceDriver->availableCommands()
            ]);
    }

    public function init(DeviceInitRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $softwareName = strtolower($request->input('software'));
            $software = Software::where('name', $softwareName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }

        $experiment = $device->getCurrentOrRequestedExperiment($software->name);

        $experiment->validateInit($request->input('input'));

        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($software->name);

        $deviceDriver->initCommand($request->input("input"));
    }

    //@Todo change for App\Http\Requests\DeviceStartRequest
    public function start(DevDeviceStartRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        try {
            $softwareName = strtolower($request->input('software'));
            $software = Software::where('name', $softwareName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorForbidden("Experiment type: '" . $type . "'" . " does not exist");
        }

        $experiment = $device->getCurrentOrRequestedExperiment($software->name);

        $experiment->validateStart($request->input('input'));

        // When everything looks fine it is
        // time to boot up classes for
        // specific device
        $deviceDriver = $device->driver($software->name);

        // We don't want to run multiple experiments
        // at the same time, on once device
        if ($deviceDriver->isRunningExperiment()) {
            throw new DeviceAlreadyRunningExperimentException;
        }

        if (App::environment() == 'local') {
            event(new ExperimentStarted($device, $experiment, $request->input('input'), 1));
            $experimentLog = $deviceDriver->startCommand($request->input("input"));
        } else {
            event(new ExperimentStarted($device, $experiment, $request->input('input'), $request->input("requested_by")));
            $experimentLog = $deviceDriver->startCommand($request->input("input"));
        }

        return $this->respondWithSuccess($experimentLog->getResult());
    }

    public function stop(DeviceStopRequest $request, $id)
    {
        try {
            $device = $this->deviceRepository->getById($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Device not found");
        }

        $deviceDriver = $device->driver();

        $deviceDriver->forceStop();

        if (!$deviceDriver->wasForceStopped()) {
            return $this->errorInternalError("Experiment did not stop");
        }

        return $this->respondWithSuccess("Experiment stopped successfully");
    }
}
