<?php

namespace App\Listeners;

use App\Events\ExperimentFinished;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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

        $logger = $event->experimentLogger;

        $logger->duration = $event->experimentDuration;
        $logger->save();

        $logger->experiment->device->detachCurrentExperiment();

    }
}
