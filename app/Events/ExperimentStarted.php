<?php

namespace App\Events;

use App\Device;
use App\Experiment;
use App\Events\Event;
use App\ExperimentLog;
use Illuminate\Queue\SerializesModels;
use App\Devices\Contracts\DeviceDriverContract;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExperimentStarted extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user_id;
    public $output_path;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExperimentLog $log)
    {
        $this->user_id = $log->requested_by;
        $this->output_path = $log->output_path;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['experiment-channel'];
    }
}
