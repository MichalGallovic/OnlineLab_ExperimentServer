<?php

namespace App\Http\Controllers;

use App\Device;
use App\Software;
use App\DeviceType;
use App\Experiment;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Devices\Helpers\CodeGenerator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CrudDeviceController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $devices = Device::paginate(15);

        return view('device.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    	$devicetypes = DeviceType::all()->lists('name','id');
    	$softwares = Software::all();
        return view('device.create', compact('devicetypes','softwares'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
    	$redirect = $this->validateDevice($request);

    	if(isset($redirect)) return $redirect;

        $input = $request->all();
        

        $device = Device::create([
        	"device_type_id" => $input["device_type"],
        	"port"	=>	$input["port"],
        	]);

        $softwares = Software::find($input["softwares"]);

        $experiments = [];

        foreach ($softwares as $software) {
        	$experiments[]=Experiment::create([
        		"device_id"	=>	$device->id,
        		"software_id"	=>	$software->id
        	]);
        }

        $defaultExperiment = Experiment::where("device_id",$device->id)->where("software_id",$input["default_software"])->first();

        $device->defaultExperiment()->associate($defaultExperiment)->save();

        $generator = new CodeGenerator($device);
        $messageBag = $generator->generateCode();

        Session::flash('flash_message', 'Device added - Code has been automatically generated for you!');

		return redirect('device')->with("messages", $messageBag->getMessages());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        $device = Device::findOrFail($id);

        return view('device.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $device = Device::findOrFail($id);
        $devicetypes = DeviceType::all()->lists('name','id');
        $softwares = Software::all();
        return view('device.edit', compact('device','devicetypes','softwares'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
    	$redirect = $this->validateDevice($request);

    	if(isset($redirect)) return $redirect;

        $device = Device::findOrFail($id);
        $updateFields = [
        	"port"	=>	$request->only("port")
        ];
        $device->update($request->only("port"));

        $input = $request->all();

        $softwares = $device->softwares->lists('id')->toArray();

		DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Experiment::where("device_id",$device->id)->whereIn("software_id",$softwares)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');


        foreach ($input["softwares"] as $software) {
        	Experiment::create([
        		"device_id"	=>	$device->id,
        		"software_id"	=>	$software
        	]);
        }

        $defaultSoftware = Software::find($input["default_software"]);
        $defaultExperiment = Experiment::where("device_id",$device->id)->where("software_id",$defaultSoftware->id)->first();
        $device->defaultExperiment()->associate($defaultExperiment)->save();

        Session::flash('flash_message_update', 'Device updated!');

        return redirect('device');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Device::destroy($id);
        Experiment::where("device_id",$id)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Session::flash('flash_message', 'Device deleted!');

        return redirect('device');
    }

    protected function validateDevice(Request $request)
    {
    	$validator = Validator::make($request->all(), [
    		'device_type'	=>	'required',
    		'port'	=>	'required',
    		'softwares'	=>	'required',
    		'default_software'	=>	"required|default_experiment :" . implode(",",$request->input('softwares'))
    		]);

    	if($validator->fails()) {
    		return redirect()->back()->withErrors($validator)->withInput();
    	}
    }
}
