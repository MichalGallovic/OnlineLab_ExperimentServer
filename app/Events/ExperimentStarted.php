<?php

namespace App\Events;

use App\Device;
use App\Experiment;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExperimentStarted extends Event
{
    use SerializesModels;

    public $device;
    public $experiment;
    public $input;
    public $requestedBy;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Device $device, Experiment $experiment, array $input, $requestedBy)
    {
        $this->device = $device;
        $this->experiment = $experiment;
        $this->input = $input;
        $this->requestedBy = $requestedBy;

        $device->currentExperiment()->associate($experiment)->save();
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
