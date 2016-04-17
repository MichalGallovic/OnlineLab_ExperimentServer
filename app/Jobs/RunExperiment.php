<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Classes\Services\ExperimentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Classes\Services\Exceptions\ExperimentCommandsNotDefined;

class RunExperiment extends Job implements ShouldQueue
{
    protected $input;

    use InteractsWithQueue;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        //
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deviceName = $this->input['device'];
        $softwareName = $this->input['software'];
        $result = "";
        try {
            $experiment = new ExperimentService($this->input, $deviceName, $softwareName);
            $result = $experiment->run();
        } catch(ModelNotFoundException $e) {
            // return $this->errorNotFound("Experiment not found");
        } catch(ExperimentCommandsNotDefined $e) {
            // return $this->setStatusCode(401)->respondWithError($e->getMessage(), 401);
        }
    }
}
