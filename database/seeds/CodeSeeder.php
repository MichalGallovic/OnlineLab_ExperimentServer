<?php

use App\User;
use App\Software;
use App\DeviceType;
use Illuminate\Database\Seeder;

class CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            "email" =>  "admin@example.com",
            "password"  =>  bcrypt('fei_admin_labak')
        ]);
    	$softwares = ["ino","matlab","openloop","openmodelica","scilab"];
    	$deviceTypes = ["tos1a","led_cube"];

    	foreach ($softwares as $software) {
    		Software::create([
    			"name"	=>	$software	
			]);
    	}

    	foreach ($deviceTypes as $deviceType) {
    		DeviceType::create([
    			"name"	=>	$deviceType
			]);
    	}
    }
}
