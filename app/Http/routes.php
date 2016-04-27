<?php

use App\Experiment;
use App\ExperimentLog;
use App\Classes\WebServer\Server;
use App\Events\ExperimentStarted;
use Illuminate\Support\Facades\Cache;
use App\Classes\Services\ExperimentService;

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api'], function() {
	
	Route::get('devices',['uses' => 'DeviceController@statusAll']);

	Route::get('devices/{id}/readexperiment',['uses' => 'DeviceController@readExperiment']);
	Route::get('devices/{id}/experiments',['uses' => 'DeviceController@previousExperiments']);
	Route::get('devices/{id}/experiments/latest',['uses' => 'DeviceController@latestExperimentOnDevice']);
	
	Route::post('devices/{id}',['uses' => 'DeviceController@executeCommand']);

	Route::get('experiments/latest',['uses' => 'ExperimentController@latest']);
	Route::get('experiments/delete',['uses' => 'ExperimentController@destroy']);
	Route::post('experiments/run', ['uses' => 'ExperimentController@run']);
	Route::post('experiments/queue', ['uses' => 'ExperimentController@queue']);
	Route::get('experiments/{id}',['uses' => 'ExperimentController@show']);

	Route::get('server/experiments',['uses' => 'ServerController@experiments']);
	Route::get('server/experiments/{id}',['uses' => 'ServerController@showExperiment']);
	Route::get('server/devices',['uses' => 'ServerController@devices']);
	Route::get('server/status',['uses' => 'ServerController@status']);

	// Development
	Route::post('file',['uses' => 'DevelopmentController@upload']);
});

Route::group(['middleware' => ['web']], function () {
	Route::get('/',['uses' => 'DevelopmentController@index']);
	Route::resource('devicetype', 'DeviceTypeController');
	Route::resource('software', 'SoftwareController');
	Route::resource('device','CrudDeviceController');
	Route::resource('experiment', 'CrudExperimentController');
	Route::get('settings', ['uses'	=>	'DevelopmentController@settings']);
	Route::get('generate', ['uses'	=>	'DevelopmentController@showGenerate']);
	Route::get("generate/device/{id}/code",['uses' => 'DevelopmentController@generateCode']);
	Route::get("reset", ['uses'	=>	'DevelopmentController@showReset']);
	Route::get("reset/database", ['uses' => 'DevelopmentController@resetDatabase']);
});

Route::get("stream/start", function() {

	$data = [
		'event'	=>	'ExperimentStarted',
		'data'	=>	[
			'user_id'	=> '4',
			'file_path' => storage_path('logs/devices/tos1a/openloop/1_1461674059.log')
		]
	];

	Redis::publish('experiment-channel', json_encode($data));

	return 'Event send! ' . storage_path('logs/devices/tos1a/openloop/1_1461674059.log');
});

Route::get("stream/end", function() {

	$data = [
		'event'	=>	'ExperimentEnded',
		'data'	=>	[
			'user_id'	=> '4'
		]
	];

	Redis::publish('experiment-channel', json_encode($data));

	return 'Event send!';
});
Route::get('test', function() {
	$data = [
		'event'	=>	'ExperimentStarted',
		'data'	=>	[
			'user_id'	=> '4',
			'file_path' => storage_path('logs/devices/tos1a/openloop/1_1461674059.log')
		]
	];

	Redis::publish('experiment-channel', json_encode($data));
});