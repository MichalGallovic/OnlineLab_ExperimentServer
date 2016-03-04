<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\ExperimentLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Classes\Transformers\ExperimentLogTransformer;
use App\Http\Requests\ExperimentLogRequest;

class ExperimentController extends ApiController
{
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
