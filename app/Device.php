<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Devices\DeviceManager;
use Illuminate\Support\Str;
use App\Devices\Exceptions\DriverDoesNotExistException;
use App\Devices\Exceptions\ExperimentNotSupportedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Devices\Exceptions\DefaultExperimentNotFoundException;

class Device extends Model
{
    
    public function driver($softwareName = null) {

        // @Todo rozbit do viacerych ?
        // Get Current / Default / Requested experiment
        $experiment = $this->getCurrentOrRequestedExperiment($softwareName);

        $method = 'create' . Str::upper($this->type->name) . Str::ucfirst($experiment->software->name) . 'Driver';

        $deviceManager = new DeviceManager($this, $experiment);

        if(!method_exists($deviceManager, $method)) {
            throw new DriverDoesNotExistException;
        }

        return $deviceManager->$method();

    }

    public function getCurrentOrRequestedExperiment($softwareName = null) {
        $softwareName = $softwareName;
        // Get current running experiment
        $experiment = $this->currentExperiment;
        if(!is_null($experiment)) return $experiment;

        // If no experiment is running and no concrete software
        // implementation is requested, return default one
        if(is_null($softwareName)) {
            $experiment = $this->getDefaultExperiment();
            return $experiment;
        }

        // If concrete software implementation is requested
        // try to find it, otherwise return exception
        return $this->getExperimentBySoftwareName($softwareName);
    }

    public function getDefaultExperiment() {
        try {
            $experiment = $this->defaultExperiment()->firstOrFail();
        } catch(ModelNotFoundException $e) {
            throw new DefaultExperimentNotFoundException($this);
        }

        return $experiment;
    }

    public function getExperimentBySoftwareName($softwareName) {
        $software = Software::where('name',$softwareName)->firstOrFail();

        try {
            $experiment = Experiment::where('software_id', $software->id)->where('device_id', $this->id)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            throw new ExperimentNotSupportedException($softwareName);
        }

        return $experiment;
    }

    public function softwares() {
        return $this->belongsToMany(Software::class,"experiments");
    }

    public function experiments() {
        return $this->hasMany(Experiment::class);
    }

    public function defaultExperiment() {
        return $this->belongsTo(Experiment::class, "default_experiment_id");
    }

    public function currentExperiment() {
        return $this->belongsTo(Experiment::class, "current_experiment_id");
    }

    public function currentExperimentLogger() {
        return $this->belongsTo(ExperimentLog::class,"current_experiment_log_id");
    }

    public function experimentLogs() {
        return $this->hasManyThrough(ExperimentLog::class,Experiment::class);
    }

    /**
     * Get current software type name
     * @return mixed [string|null]
     */
    public function currentSoftwareName() {
        $experiment = $this->currentExperiment;

        return is_null($experiment) ? null : $experiment->software->name;
    }

    public function detachCurrentExperiment() {
        $this->current_experiment_id = null;
        $this->current_experiment_log_id = null;
        $this->save();
    }

    public function type() {
        return $this->belongsTo(DeviceType::class,'device_type_id');
    }
}
