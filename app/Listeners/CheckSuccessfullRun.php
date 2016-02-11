<?php

namespace App\Listeners;

use App\Events\ProcessWasRan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\ProcessLog;
use App\FailedProcessLog;

class CheckSuccessfullRun
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
        // Checks if the process ended without errors

        if(!$event->process->isSuccessful()) {
            $this->logError($event);   
        }
    }

    protected function logError($event) {
        // Create a verbal representation of a process error
        $exception = new ProcessFailedException($event->process);
        // Get latest process log (the one that failed)
        $loggedProcess = ProcessLog::where('device_id',$event->device->id)
                        ->latest()->first();

        // Log error to database
        $errorLogger = new FailedProcessLog;
        $errorLogger->process_log_id = $loggedProcess->id;
        $errorLogger->reason = $exception->getMessage();
        $errorLogger->save();

        // @TODO
        // fire event to notify admin about a problem

        // we could also parse the exit code and at correct reason
    }
}
