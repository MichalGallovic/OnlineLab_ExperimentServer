<?php

use Illuminate\Database\Seeder;
use App\ExperimentType;

class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$uuid = 12345;

    	App\Device::create([
    		"uuid" => $uuid++,
			"port" => "/dev/ttyACM0",
			"device_type" => "tos1a"
		]);

    	$experiments = ExperimentType::all();
   
    	foreach ($experiments as $experiment) {
    		App\Device::create([
    			"uuid" => $uuid++,
    			"port" => "/dev/ttyACM0",
    			"device_type" => "tos1a",
    			"experiment_type_id" => $experiment->id
    		]);	
    	}
    }
}
