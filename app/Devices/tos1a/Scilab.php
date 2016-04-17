<?php

namespace App\Devices\tos1a;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Scripts\StopScript;
use App\Devices\Scripts\StartScript;
use App\Devices\Scripts\tos1a\scilab\StartScriptScilab;
use App\Devices\Contracts\DeviceDriverContract;

class Scilab extends AbstractDevice implements DeviceDriverContract {


	/**
     * Paths to read/stop/run scripts relative to
     * $(app_root)/server_scripts folder
     * @var array
     */
	protected $scriptPaths = [
        "start"  => "tos1a/scilab/start_sci",
        "change"	=> "",
        "stop"=> "tos1a/stop.py"
    ];

    /**
     * Construct base class (App\Devices\AbstractDevice)
     * @param Device     $device     Device model from DB
     * @param Experiment $experiment Experiment model from DB
     */
    public function __construct(Device $device, Experiment $experiment)
    {
        parent::__construct($device,$experiment);
    }

    protected function start($input)
    {
        //var_dump($input);
        // regulovana velicina vychadzajuca zo sustavy
        switch ($input["out_sw"]) {
        case "Temperature":
            $input["out_sw"] = "1";
            break;
        case "Light Intensity":
            $input["out_sw"] = "2";
            break;
        case "Fan RPM":
            $input["out_sw"] = "3";
            break;
        }
        // upravovana velicina vchadzajuca do sustavy
        switch ($input["in_sw"]) {
        case "Bulb":
            $input["in_sw"] = "1";
            break;
        case "Led":
            $input["in_sw"] = "2";
            break;
        case "Fan":
            $input["in_sw"] = "3";
            break;
        }  
        // rozhodnutie ci ide o vlastny regulator alebo PID regulator
        switch ($input["own_ctrl"]) {
        case "PID":
            $input["own_ctrl"] = "0";
            break;
        case "Own function":
            $input["own_ctrl"] = "1";
            break;
        } 
        // prisposobenie vlastnej funkcie pre citatelnost v scilabe  
        
        $input["ts"] = ($input["ts"]) * (0.001); 

        if( is_file($input["uploaded_file"]) && file_exists( $input["uploaded_file"]) ) $input["own_ctrl"] = "2";
        $input["uploaded_file"] = "'".$input["uploaded_file"] ."'";
        
        // reset required value on 0 in file, it is then loaded in scilab scheme, required value could be changed in change function
        $serverPath = str_replace("/public", "", $_SERVER["DOCUMENT_ROOT"]);
        $fileChange = "$serverPath/server_scripts/tos1a/scilab/shm/change_input";
        file_put_contents("$fileChange", "0");

        $script = new StartScriptScilab(
            $this->scriptPaths["start"],
            $input,
            $this->device,
            $this->experimentLog->output_path
            );

        $script->run();

    }

    protected function change($input)
    {
        $serverPath = str_replace("/public", "", $_SERVER["DOCUMENT_ROOT"]);
        $fileChange = "$serverPath/server_scripts/tos1a/scilab/shm/change_input";

        file_put_contents("$fileChange", $input["required_value"]);

    }

    protected function stop($input)
    {
        $script = new StopScript(
                $this->scriptPaths["stop"],
                $this->device
            );

        $script->run();

    }



    protected function parseDuration($input)
    {
        return $input["time"];
    }

    protected function parseSamplingRate($input)
    {
        return $input["ts"];
    }

}