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

// use Symfony\Component\Process\Process;
// use Symfony\Component\Process\Exception\ProcessFailedException;
// use App\Device;
// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/api/devices/{uuid}', function($uuid) {
// 	try {
// 		$device = Device::where('uuid',$uuid)->firstOrFail();

// 	} catch(Exception $e) {
// 		return "Sorry no such device here :(";
// 	}

// 	$process = new Process('/home/vagrant/Desktop/readonce.py ' . $device->path);
// 	$process->run();
// 	return $process->getOutput();
// });

// Route::get('/api/devices/{uuid}/run', function($uuid) {
// 	// try {
// 	// 	$device = Device::where('uuid',$uuid)->firstOrFail();

// 	// } catch(Exception $e) {
// 	// 	return "Sorry no such device here :(";
// 	// }
// 	// $process = new Process('/var/www/run.py > /dev/null 2>/dev/null &');
// 	// $process->run();

// 	$process = new Process('/home/vagrant/Desktop/run.py > /dev/null 2>/dev/null &');
// 	$process->run();

// 	$process = new Process('/home/vagrant/api/server_scripts/createfile_works.py '  . "/dev/ttyACM0" . " " . $uuid . " 15" .' > /dev/null 2>/dev/null &');
// 	$process->run();

// 	if(!$process->isSuccessful()) {
// 		throw new ProcessFailedException($process);
// 	}
// });
// Route::get('/api/devices/{uuid}/stop', function($uuid) {
// 	// try {
// 	// 	$device = Device::where('uuid',$uuid)->firstOrFail();

// 	// } catch(Exception $e) {
// 	// 	return "Sorry no such device here :(";
// 	// }
// 	$process = new Process('/home/vagrant/Desktop/stop.py');
// 	$process->run();

// 	return $process->getOutput();
// });

// Route::get('/api/devices/{uuid}/read', function($uuid) {
// 	// try {
// 	// 	$device = Device::where('uuid',$uuid)->firstOrFail();

// 	// } catch(Exception $e) {
// 	// 	return "Sorry no such device here :(";
// 	// }
// 	// $process = new Process('cat /home/vagrant/api/files/' . $uuid);
// 	// $process->run();


// 	return $process->getOutput();
// });


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['prefix' => 'api'], function() {
	/*
	* GET JSON OF ALL EXPERIMENTS
	* [
	*	{
	*		uuid : 123441,
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
	Route::get('devices/{uuid}',['uses' => 'DeviceController@statusOne']);
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
	Route::post('devices/{uuid}/run',['uses' => 'DeviceController@run']);
	/*
	* STOP experiment
	* with type, so we can also do some clean up, like stopping
	* matlab|modelica|scilab or run loop process
	*
	* {
	*	type: matlab|modelica|scilab|loop 
	* }
 	*/
	Route::post('devices/{uuid}/stop',['uses' => 'DeviceController@stop']);


});

Route::group(['middleware' => ['web']], function () {
    //
});
