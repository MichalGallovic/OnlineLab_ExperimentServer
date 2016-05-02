<?php

namespace App\Http\Controllers;

use App\Experiment;
use App\Http\Requests;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Artisan;
use App\Classes\Services\SystemStatusService;
use App\Classes\Transformers\DeviceTransformer;
use App\Devices\Contracts\DeviceDriverContract;
use App\Classes\Repositories\DeviceDbRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Classes\Transformers\AvailableExperimentTransformer;

class ServerController extends ApiController
{
	protected $device;

	public function __construct(DeviceDbRepository $device, Manager $fractal)
	{
		parent::__construct($fractal);
		$this->device = $device;
	}

    public function experiments(Request $request)
    {
    	$experimnets = Experiment::all();
        
    	return $this->respondWithCollection($experimnets, new AvailableExperimentTransformer);
    }

    public function showExperiment(Request $request, $id)
    {
    	try {
    		$experiment = Experiment::findOrFail($id);
    	} catch(ModelNotFoundException $e) {
    		return $this->errorNotFound("Experiment not found!");
    	}

    	return $this->respondWithItem($experiment, new AvailableExperimentTransformer);
    }

    public function devices(Request $request)
    {
    	$devices = $this->device->getAll();

        // @Todo comamnd should will not be called on every
        // api request it will be scheduled with a cron job
        // but we could call it when requested with a 
        // specific api_token ? user permissions ? Guard?
        // Artisan::call('server:devices:ping');
        
        

        return $this->respondWithCollection($devices, new DeviceTransformer);
    }

    public function status(Request $request)
    {
        $status = new SystemStatusService();
        $status->check();

        return $status->response();
    }
}
