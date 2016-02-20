<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\ExperimentLog;

class ExperimentFinished extends Event
{
    use SerializesModels;

    public $experimentLogger;
    public $experimentDuration;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExperimentLog $experimentLogger, $experimentDuration)
    {
        $this->experimentLogger = $experimentLogger;
        $this->experimentDuration = $experimentDuration;
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
