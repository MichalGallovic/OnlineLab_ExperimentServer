<?php

namespace App\Http\Controllers;

use App\DeviceType;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DevelopmentController extends Controller
{
	public function index(Request $request)
	{
		return view('experiments.dashboard');
	}

	public function settings(Request $request)
	{
		return view('experiments.settings');
	}

	public function deviceTypes(Request $request)
	{
		$deviceTypes = DeviceType::all();
		return view('device_types.index')->with(["device_types" => $deviceTypes]);
	}

	public function softwares(Request $request)
	{
		return view('experiments.settings');
	}

	public function physicalDevices(Request $request)
	{
		return view('experiments.settings');
	}

	public function experiments(Request $request)
	{
		return view('experiments.settings');
	}
}
