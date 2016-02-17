<?php

use Illuminate\Database\Seeder;
use App\ExperimentType;
use App\DeviceType;
class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	$tos1a = DeviceType::where("name","tos1a")->first();

    	App\Device::create([
    		"uuid" => 12345,
			"port" => "/dev/ttyACM0",
			"device_type_id" => $tos1a->id
		]);
    }
}
