<?php

use Illuminate\Database\Seeder;

use App\Experiment;
use App\DeviceType;
use App\Software;
use App\Device;

class DefaultExperimentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $software = Software::where('name','openloop')->first();
        $experiment = Experiment::where('software_id',$software->id)->first();

        $device = Device::whereHas('type', function($query) {
        	$query->where('name','tos1a');
        })->first();

        $device->defaultExperiment()->associate($experiment)->save();
    }
}
