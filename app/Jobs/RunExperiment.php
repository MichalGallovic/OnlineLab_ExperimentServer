<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Http\Request;
use App\Classes\WebServer\Server;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Classes\Services\ExperimentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Devices\Exceptions\ParametersInvalidException;
use App\Devices\Exceptions\DeviceNotConnectedException;
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
            $experiment->updateStatusWS("ready");
            $experiment->updateReportWs($experiment->getExperimentLog(), $this->input["report_id"]);
        } catch(DeviceNotConnectedException $e) {
            var_dump("Device is not connected :(");
        } catch(ParametersInvalidException $e) {
            var_dump("Parameters invalid, sorry :(");
        }
    }

    /**
     * Gets the value of experimentLog.
     *
     * @return mixed
     */
    public function getExperimentLog()
    {
        return $this->experimentLog;
    }
}
