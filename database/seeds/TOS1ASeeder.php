<?php

use Illuminate\Database\Seeder;
use App\DeviceType;
use App\Device;
use App\Software;

class TOS1ASeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tos1a = DeviceType::where("name","tos1a")->first();


    	$device = App\Device::create([
			"port" => "/dev/ttyACM0",
			"device_type_id" => $tos1a->id
		]);

    	$swNames = ["openloop", "matlab"];
		$softwares = Software::whereIn('name',$swNames)->get();

		foreach ($softwares as $software) {
			$device->softwares()->attach($software->id);
		}

		$device->save();

		$defaultSoftware = Software::where('name',"openloop")->first();

		$device->defaultExperiment()->associate($defaultSoftware)->save();
    }
}
