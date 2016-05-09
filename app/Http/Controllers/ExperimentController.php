<?php

namespace App\Http\Controllers;

use App\Device;
use App\ExperimentLog;
use App\Http\Requests;
use App\Jobs\RunExperiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Classes\Services\ExperimentService;
use App\Http\Requests\ExperimentLogRequest;
use App\Http\Requests\ExperimentRunRequest;
use App\Classes\Transformers\ExperimentLogTransformer;
use App\Classes\Validators\ExperimentServiceValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Classes\Services\Exceptions\ExperimentCommandsNotDefined;

class ExperimentController extends ApiController
{

    public function queue(ExperimentRunRequest $request)
    {
        Log::info($request->input());
        $this->dispatch(new RunExperiment($request->input()));
        return $this->respondWithSuccess("Request received");
    }

    public function run(ExperimentRunRequest $request)
    {
        $deviceName = $request->input('device');
        $softwareName = $request->input('software');
        
        try {
            $experiment = new ExperimentService($request->input(), $deviceName, $softwareName);
            $result = $experiment->run();
        } catch(ModelNotFoundException $e) {
            return $this->errorNotFound("Experiment not found");
        } catch(ExperimentCommandsNotDefined $e) {
            return $this->setStatusCode(401)->respondWithError($e->getMessage(), 401);
        }

        return $this->respondWithSuccess($result);
    }

    public function history(Request $request) 
    {

    }

    public function show(ExperimentLogRequest $request, $id) 
    {
    	try {
			$experiment = ExperimentLog::findOrFail($id);
		} catch(ModelNotFoundException $e) {
			return $this->errorNotFound("Experiment not found!");
		}

		$measurementsEvery = $experiment->measuring_rate;

        if($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

		return $this->respondWithItem($experiment, new ExperimentLogTransformer($measurementsEvery));

    }

    public function destroy(Request $request)
    {
        $experimentLogs = ExperimentLog::all();

        $devices = Device::all();

        foreach ($devices as $device) {
            $device->detachCurrentExperiment();
        }

        foreach ($experimentLogs as $experimentLog) {
            $experimentLog->delete();
        }

        return redirect()->back();
    }

    public function latest(Request $request)
    {
		try {
			$experiment = ExperimentLog::latest()->firstOrFail();
		} catch(ModelNotFoundException $e) {
			return $this->errorNotFound("There was no experiment executed yet!");
		}

		$measurementsEvery = $experiment->measuring_rate;

        if($request->has("every")) {
            $measurementsEvery = $request->input("every");
        }

		return $this->respondWithItem($experiment, new ExperimentLogTransformer($measurementsEvery));
    }


}
