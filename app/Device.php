<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Devices\DeviceManager;
use Illuminate\Support\Str;
use App\Devices\Exceptions\DriverDoesNotExistException;

class Device extends Model
{
    /**
     * @param  $experimentType = matlab|scilab|openmodelica|loop|null
     * @return DeviceDriverContract - concrete implementation
     */
    public function driver($experimentType = null) {
    	$deviceManager = new DeviceManager($this);

        // when experiment type is null, there is no experiment
        //  running on the device
        if(is_null($this->currentExperimentType)) {
            // when $experimentType is null, we are just checking
            // the state of the device
            $experimentType = is_null($experimentType) ? "loop" : $experimentType;
        } else {
            // otherwise, we use the experiment is running,
            // so we will instantiate the concrete type
            // of device driver implementation
            $experimentType = $this->currentExperimentType->name;
        }

        // we create the method name, so we can instantiate the 
        // correct DeviceDriverContract implementation
        // i.e. createTOS1AMatlab
    	$method = 'create' . Str::upper($this->type->name) . Str::ucfirst($experimentType) . 'Driver';

    	if(!method_exists($deviceManager, $method)) {
            throw new DriverDoesNotExistException;
    	}

    	return $deviceManager->$method();
    }

    public function experimentTypes() {
        return $this->belongsToMany(ExperimentType::class,"experiments");
    }

    public function currentExperimentType() {
        return $this->belongsTo(ExperimentType::class, "current_experiment_type_id");
    }

    public function type() {
        return $this->belongsTo(DeviceType::class,'device_type_id');
    }
}
