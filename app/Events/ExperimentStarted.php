<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Device;
use App\ExperimentType;

class ExperimentStarted extends Event
{
    use SerializesModels;

    public $device;
    public $experimentType;
    public $input;
    public $requestedBy;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Device $device, ExperimentType $experimentType, array $input, $requestedBy)
    {
        $this->device = $device;
        $this->experimentType = $experimentType;
        $this->input = $input;
        $this->requestedBy = $requestedBy;

        $device->currentExperimentType()->associate($experimentType)->save();
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
