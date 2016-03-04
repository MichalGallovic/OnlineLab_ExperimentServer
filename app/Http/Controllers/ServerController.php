<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Experiment;
use App\Http\Controllers\ApiController;
use App\Classes\Transformers\AvailableExperimentTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServerController extends ApiController
{
    public function experiments(Request $request)
    {
    	$experimnets = Experiment::all();

    	return $this->respondWithCollection($experimnets, new AvailableExperimentTransformer);
    }

    public function show(Request $request, $id)
    {
    	try {
    		$experiment = Experiment::findOrFail($id);
    	} catch(ModelNotFoundException $e) {
    		return $this->errorNotFound("Experiment not found!");
    	}

    	return $this->respondWithItem($experiment, new AvailableExperimentTransformer);
    }
}
