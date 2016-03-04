<?php

use Illuminate\Database\Seeder;
use App\Experiment;
use App\DeviceType;
use App\ExperimentType;
use App\Device;

class ExperimentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $experimentTypes = ExperimentType::all();
        $devices = Device::all();

        foreach ($devices as $device) {
            foreach ($experimentTypes as $experimentType) {
                Experiment::create([
                    "device_id" => $device->id,
                    "experiment_type_id" => $experimentType->id
                ]);
            }
        }
    }
}
