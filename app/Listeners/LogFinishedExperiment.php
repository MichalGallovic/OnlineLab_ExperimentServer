<?php

namespace App\Listeners;

use App\Devices\Contracts\DeviceDriverContract;
use App\Events\ExperimentFinished;
use Carbon\Carbon;

class LogFinishedExperiment
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
     * @param  ExperimentFinished  $event
     * @return void
     */
    public function handle(ExperimentFinished $event)
    {
        $event->device->status = DeviceDriverContract::STATUS_READY;
        $event->device->save();

        $logger = $event->device->currentExperimentLogger;
        $logger->finished_at = Carbon::now();
        $logger->save();
    }
}
