<?php

namespace App\Classes\Repositories;

use App\Device;

class DeviceDbRepository {

	public function getById($id) {
		$device = Device::findOrFail($id);

		return $device;
	}

	public function getAll() {
		$devices = Device::all();

		return $devices;
	}
}