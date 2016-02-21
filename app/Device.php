<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Devices\DeviceManager;
use Illuminate\Support\Str;
use App\Devices\Exceptions\DriverDoesNotExistException;
use App\Devices\Exceptions\ExperimentNotSupportedException;

class Device extends Model
{
    /**
     * @param  $experimentType = matlab|scilab|openmodelica|loop|null
     * @return DeviceDriverContract - concrete implementation
     */
    public function driver($experimentType = null) 
    {
        // when experiment type is null, there is no experiment
        //  running on the device
        $type = $this->currentExperimentType;
        if(is_null($type)) {
            // when $experimentType is null, we are just checking
            // the state of the device
            $experimentType = is_null($experimentType) ? "openloop" : $experimentType;
        } else {
            // otherwise, we use the experiment is running,
            // so we will instantiate the concrete type
            // of device driver implementation
            $experimentType = $type->name;
        }

        $availableExperiments = $this->experimentTypes->lists("name")->toArray();

        if(!in_array(strtolower($experimentType), $availableExperiments)) {
            throw new ExperimentNotSupportedException($experimentType);
        }

        // we create the method name, so we can instantiate the 
        // correct DeviceDriverContract implementation
        // i.e. createTOS1AMatlab
    	$method = 'create' . Str::upper($this->type->name) . Str::ucfirst($experimentType) . 'Driver';

        $deviceManager = new DeviceManager($this, ExperimentType::where("name", $experimentType)->first());

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

    public function currentExperimentLogger() {
        return $this->belongsTo(ExperimentLog::class,"current_experiment_log_id");
    }

    /**
     * Get current experiment type name
     * @return mixed [string|null]
     */
    public function currentExperimentName() {
        $type = $this->currentExperimentType;

        if(is_null($type)) {
            return null;
        }

        return $type->name;
    }

    public function detachCurrentExperiment() {
        $this->current_experiment_type_id = null;
        $this->current_experiment_log_id = null;
        $this->save();
    }

    public function type() {
        return $this->belongsTo(DeviceType::class,'device_type_id');
    }
}
