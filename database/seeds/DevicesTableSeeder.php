<?php

use Illuminate\Database\Seeder;


class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	App\Device::create([
    		"uuid" => "12345",
    		"port" => "/dev/ttyACM0",
    		"device_type" => "tos1a"
    	]);

    	App\Device::create([
    		"uuid" => "12346",
    		"port" => "/dev/ttyACM0",
    		"device_type" => "tos1a",
    		"experiment_type" => "matlab"
    	]);

    	App\Device::create([
    		"uuid" => "12347",
    		"port" => "/dev/ttyACM0",
    		"device_type" => "tos1a",
    		"experiment_type" => "scilab"
    	]);

    	App\Device::create([
    		"uuid" => "12348",
    		"port" => "/dev/ttyACM0",
    		"device_type" => "tos1a",
    		"experiment_type" => "openmodelica"
    	]);
    }
}
