<?php

use Illuminate\Database\Seeder;
use App\DeviceType;

class DeviceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DeviceType::create([
        	"name" => "tos1a"
    	]);
    }
}
