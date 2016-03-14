<?php

use Illuminate\Database\Seeder;
use App\Software;

class SoftwaresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Software::create([
        	"name" => "openloop"
        ]);
        Software::create([
        	"name" => "matlab"
        ]);
        // SoftwareEnvironment::create([
        // 	"name" => "scilab"
        // ]);
        // SoftwareEnvironment::create([
        // 	"name" => "openmodelica"
        // ]);
    }
}
