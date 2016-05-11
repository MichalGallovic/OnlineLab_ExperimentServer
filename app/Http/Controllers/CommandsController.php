<?php

namespace App\Http\Controllers;

use App\Device;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Classes\Services\CommandService;

class CommandsController extends Controller
{
    public function stop(Request $request)
    {
    	$device = Device::where('name',$request->input('instance'))->whereHas('type', function($q) use ($request) {
    		$q->where('name',$request->input('device'));
    	})->first();
    	$command = new CommandService(
    		["command" => "stop", "software" => $request->input('software')], $device->id
    	);
    	$command->execute();
    }

    public function change(Request $request)
    {
        $device = Device::where('name',$request->input('instance'))->whereHas('type', function($q) use ($request) {
            $q->where('name',$request->input('device'));
        })->first();
        $command = new CommandService(
            [
                "command" => "change", 
                "software" => $request->input('software'),
                "input" =>  $request->input('input')
            ], $device->id
        );
        $command->execute();
    }
}
