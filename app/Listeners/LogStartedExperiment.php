<?php

namespace App\Listeners;

use App\Events\ExperimentStarted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ExperimentLog;
use App\Experiment;

class LogStartedExperiment
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExperimentStarted  $event
     * @return void
     */
    public function handle(ExperimentStarted $event)
    {
        $logger = new ExperimentLog;
        $experiment = Experiment::where("device_id", $event->device->id)->where("experiment_type_id", $event->experimentType->id)->first();
        $logger->experiment()->associate($experiment);
        $logger->input_arguments = json_encode($event->input);
        $output_path = storage_path("logs/experiments/" . strtolower($event->device->type->name) . "/" . strtolower($event->experimentType->name));
        $logger->output_path = $output_path;
        $logger->requested_by = $event->requestedBy;
        $logger->save();

        return $logger;
    }
}
