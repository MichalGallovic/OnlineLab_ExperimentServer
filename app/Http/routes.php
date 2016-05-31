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

Route::group(['prefix' => 'api', 'middleware' => ['webserver.auth']], function() {
	
	Route::get('devices',['uses' => 'DeviceController@statusAll']);

	Route::get('devices/{id}/readexperiment',['uses' => 'DeviceController@readExperiment']);
	Route::get('devices/{id}/experiments',['uses' => 'DeviceController@previousExperiments']);
	Route::get('devices/{id}/experiments/latest',['uses' => 'DeviceController@latestExperimentOnDevice']);
	
	Route::post('devices/{id}',['uses' => 'DeviceController@executeCommand']);

	Route::post('commands/stop', ['uses' => 'CommandsController@stop']);
	Route::post('commands/change', ['uses' => 'CommandsController@change']);

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

Route::group(['middleware' => ['web','webserver.auth']], function () {
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


Route::get("test", function() {
	
});