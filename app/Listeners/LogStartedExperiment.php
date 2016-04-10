<?php

namespace App\Listeners;

use App\Events\ExperimentStarted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ExperimentLog;
use App\Experiment;
use App\Devices\Contracts\DeviceDriverContract;

class LogStartedExperiment
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
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
        $logger->experiment()->associate($event->experiment);
        $logger->input_arguments = json_encode($event->input);
        
        $logger->requested_by = $event->requestedBy;
        $logger->save();

        $event->device->status = DeviceDriverContract::STATUS_EXPERIMENTING;
        $event->device->currentExperiment()->associate($event->experiment)->save();
        $event->device->currentExperimentLogger()->associate($logger)->save();
    }
}
