<?php

use Illuminate\Database\Seeder;
use App\Experiment;
use App\DeviceType;
use App\ExperimentType;


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
        $tos1a = DeviceType::where("name","tos1a")->first()->devices->first();

        foreach ($experimentTypes as $type) {
        	Experiment::create([
        		"device_id" => $tos1a->id,
        		"experiment_type_id" => $type->id
        	]);
        }
    }
}
