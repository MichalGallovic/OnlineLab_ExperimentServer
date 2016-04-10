<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Symfony\Component\Process\Process;
use App\Device;
use App\ProcessLog;
/**
 * Event that is raised when
 * a process was run
 */
class ProcessWasRan extends Event
{
    use SerializesModels;

    public $process;
    public $device;
    public $process_log;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Process $process, Device $device)
    {
        // Log process to database
        $logger = new ProcessLog;
        $logger->device_id = $device->id;
        $logger->command = $process->getCommandLine();
        $logger->save();

        $this->process = $process;
        $this->device = $device;
        $this->process_log = $logger;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
