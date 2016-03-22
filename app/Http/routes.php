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
	Route::get('devices/{id}',['uses' => 'DeviceController@statusOne']);
	Route::get('devices/{id}/read',['uses' => 'DeviceController@readOne']);
	Route::get('devices/{id}/readexperiment',['uses' => 'DeviceController@readExperiment']);
	Route::get('devices/{id}/experiments',['uses' => 'DeviceController@previousExperiments']);
	Route::get('devices/{id}/experiments/latest',['uses' => 'DeviceController@latestExperimentOnDevice']);
	
	Route::post('devices/{id}/init',['uses' => 'DeviceController@init']);
	Route::post('devices/{id}/start',['uses' => 'DeviceController@start']);
	Route::post('devices/{id}/stop',['uses' => 'DeviceController@stop']);
	Route::post('devices/{id}/change',['uses' => 'DeviceController@change']);
	Route::post('devices/{id}/commands',['uses' => 'DeviceController@listCommands']);

	Route::get('experiments/latest',['uses' => 'ExperimentController@latest']);
	Route::get('experiments/{id}',['uses' => 'ExperimentController@show']);

	Route::get('server/experiments',['uses' => 'ServerController@experiments']);
	Route::get('server/experiments/{id}',['uses' => 'ServerController@showExperiment']);
	Route::get('server/devices',['uses' => 'ServerController@devices']);

});

Route::group(['middleware' => ['web']], function () {
	Route::get('/',['uses' => 'DevelopmentController@index']);
	Route::get('/testik', function() {
		$input = App\Experiment::first()->getInputArgumentsNames();
		$input = collect($input);
		$output = $input->__toString();
		$output = str_replace("[","",$output);
		$output = str_replace("]","",$output);
		return $output;
	});
});
