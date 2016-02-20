<?php

use Illuminate\Database\Seeder;
use App\ExperimentType;

class ExperimentTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExperimentType::create([
        	"name" => "openloop"
        ]);
        ExperimentType::create([
        	"name" => "matlab"
        ]);
        ExperimentType::create([
        	"name" => "scilab"
        ]);
        ExperimentType::create([
        	"name" => "openmodelica"
        ]);
    }
}
