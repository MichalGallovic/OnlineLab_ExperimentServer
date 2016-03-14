<?php

use Illuminate\Database\Seeder;
use App\Experiment;
use App\DeviceType;
use App\Software;
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
        $softwares = Software::all();
        $devices = Device::all();

        foreach ($devices as $device) {
            foreach ($softwares as $software) {
                Experiment::create([
                    "device_id" => $device->id,
                    "software_id" => $software->id
                ]);
            }
        }
    }
}
