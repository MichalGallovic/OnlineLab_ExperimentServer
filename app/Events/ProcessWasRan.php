<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Symfony\Component\Process\Process;
use App\Device;

/**
 * Event that is raised when
 * a process was run
 */
class ProcessWasRan extends Event
{
    use SerializesModels;

    public $process;
    public $device;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Process $process, Device $device)
    {
        $this->process = $process;
        $this->device = $device;
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
