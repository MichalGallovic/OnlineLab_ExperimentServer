<?php

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
	Route::get('experiments/{id}',['uses' => 'ExperimentController@show']);

	Route::get('server/experiments',['uses' => 'ServerController@experiments']);
	Route::get('server/experiments/{id}',['uses' => 'ServerController@showExperiment']);
	Route::get('server/devices',['uses' => 'ServerController@devices']);

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
