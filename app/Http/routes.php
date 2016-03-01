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


Route::get('testik', function() {
	$process = new Process("pstree -p 1 | grep -o '([0-9]\+)' | grep -o '[0-9]\+'");
	$process->run();

	return array_filter(explode("\n",$process->getOutput()));
});

Route::group(['prefix' => 'api'], function() {
	/*
	* GET JSON OF ALL EXPERIMENTS
	* [
	*	{
	*		id : 123441,
	*		status: offline | online | experiment
	*	},
	*	...
	* ]
	*/
	Route::get('devices',['uses' => 'DeviceController@statusAll']);
	/*
	* GET status of concrete device
	* {
	*	status: offline | online | experiment
	* }
	*/
	Route::get('devices/{id}',['uses' => 'DeviceController@statusOne']);
	Route::get('devices/{id}/read',['uses' => 'DeviceController@readOne']);
	Route::get('devices/{id}/readexperiment',['uses' => 'DeviceController@readExperiment']);
	Route::get('devices/{id}/experiments',['uses' => 'DeviceController@previousExperiments']);
	// Route::get('devices/{id}/readexperiment',['uses' => 'DeviceController@readExperiment'])
	/*
	* RUN experiment on concrete device
	* with params, with or without environment
	*
	* {
	*	type: matlab|modelica|scilab|loop,
	*	args: {
	*		P : 1
	*		I : 0.5
	*		D : 0.8
	*		...
	*	} 
	* }
 	*/
	Route::post('devices/{id}/run',['uses' => 'DeviceController@run']);
	/*
	* STOP experiment
	* with type, so we can also do some clean up, like stopping
	* matlab|modelica|scilab or run loop process
	*
	* {
	*	type: matlab|modelica|scilab|loop 
	* }
 	*/
	Route::get('devices/{id}/stop',['uses' => 'DeviceController@stop']);


});

Route::group(['middleware' => ['web']], function () {
    //
});
