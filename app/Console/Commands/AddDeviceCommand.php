<?php

namespace App\Console\Commands;

use App\DeviceType;
use App\Software;
use App\Experiment;
use App\Device;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class AddDeviceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:devices:add {deviceName : The name of the new device type }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new device to project';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deviceName = $this->argument('deviceName');

        if(!$this->isUnique($deviceName)) {
            return $this->error("Device " . $deviceName . " already exists in the system.");
        }

        $softwares = $this->askForSoftwares();

        if(count($softwares) == 0) {
            return $this->error("You did not pick any software!");
        }

        $defaultSoftware = null;

        if(count($softwares) == 1) {
            $defaultSoftware = $softwares[0];
        } else {
            $defaultSoftware = $this->askForDefaultSoftware($softwares);
        }

        $port = $this->askForPort();

        //@todo generate permanent seeder ?

        Model::unguard();

        // Create device type
        $deviceType = DeviceType::create([
            "name"  =>  $deviceName
        ]);

        // Create device
        $device = Device::create([
            "port" => $port,
            "device_type_id" => $deviceType->id
        ]);

        // Create experiments
        foreach ($softwares as $software) {
            $device->softwares()->attach($software->id);
        }

        $device->save();

        // Set default experiment
        $device->defaultExperiment()->associate($defaultSoftware)->save();

        $experiments_path = storage_path("logs/experiments");

        foreach ($softwares as $software) {
            $software_path = $experiments_path . "/" . $device->type->name . "/" . $software->name;
            File::makeDirectory($software_path, 0775, true, true);
        }

    }

    protected function isUnique($deviceName) {
        $count = DeviceType::where('name',$deviceName)->count();
        return $count > 0 ? false : true;
    }

    protected function askForSoftwares() {
        $softwares = Software::all();

        $this->info("Which software environments, would you like to implement for this device?");

        $userWants = [];

        foreach ($softwares as $software) {
            if($this->confirm($software->name . " [y|N]")) {
                $userWants[]=$software;
            }
        }

        return $userWants;
    }

    protected function askForDefaultSoftware($softwares) {
        $this->info("Which of desired software environments should be default one ?");

        $question = "";

        foreach ($softwares as $index => $software) {
            $question .= $software->name . " ($index) ";
        }

        $defaultSoftware = 99999;

        while(abs(intval($defaultSoftware)) >= count($softwares) ||
            !is_numeric($defaultSoftware)
            ) 
        {
            $defaultSoftware = $this->ask($question);
        }

        $defaultSoftware = abs(intval($defaultSoftware));

        return $softwares[$defaultSoftware];
    }

    // @todo this could be better
    protected function askForPort() {
        $port = $this->ask("Last one. Type in serial/usb port, your device is connected to right now", "/dev/ttyACM0");

        $port = str_replace("\"", "", $port);

        if($port[0] != "/") $port = "/" . $port;

        return $port;
    }

}
