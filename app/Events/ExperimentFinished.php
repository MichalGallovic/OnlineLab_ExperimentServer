<?php

namespace App\Events;

use App\Device;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ExperimentFinished extends Event {
	use SerializesModels;

	public $device;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(Device $device) {
		$this->device = $device;
	}

	/**
	 * Get the channels the event should be broadcast on.
	 *
	 * @return array
	 */
	public function broadcastOn() {
		return [];
	}
}
