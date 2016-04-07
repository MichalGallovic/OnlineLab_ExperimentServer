<?php

namespace App\Http\Controllers;

use App\DeviceType;
use App\Experiment;
use App\Http\Requests;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Devices\Helpers\CodeGenerator;
use Illuminate\Support\Facades\Artisan;

class DevelopmentController extends Controller
{
	public function index(Request $request)
	{
		return view('experiments.dashboard');
	}

	public function settings(Request $request)
	{
		return redirect('device');
	}

	public function showGenerate(Request $request)
	{
		$experiments = Experiment::all();
		return view('generate', compact('experiments'));
	}

	public function generateCode(Request $request, $id)
	{
		$experiment = Experiment::find($id);
		$generator = new CodeGenerator($experiment);
		$messages = $generator->generateCode();

		return redirect()->back()->with("messages", $messages->getMessages());
	}

	

	public function showReset(Request $request)
	{
		return view('reset');
	}

	public function resetDatabase(Request $request)
	{
		Artisan::call('server:reset');
		return redirect()->back();
	}
}
