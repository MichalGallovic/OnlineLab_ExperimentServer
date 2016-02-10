<?php

namespace App\Listeners;

use App\Events\ProcessWasRan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Listeners\LogProcess;
use App\ProcessLog;

class LogProcess
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
     * @param  ProcessWasRan  $event
     * @return void
     */
    public function handle(ProcessWasRan $event)
    {
        // Log process to database
        $logger = new ProcessLog;
        $logger->device_id = $event->device->id;
        $logger->command = $event->process->getCommandLine();
        $logger->save();
    }
}
