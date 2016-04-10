<?php

namespace App;

use App\Devices\DeviceManager;
use App\Devices\Exceptions\DefaultExperimentNotFoundException;
use App\Devices\Exceptions\DriverDoesNotExistException;
use App\Devices\Exceptions\ExperimentNotSupportedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\DeviceNotConnectedException;

class Device extends Model
{
    protected $fillable = ["device_type_id","port"];
    public function driver($softwareName = null)
    {
        if(\App::environment() != 'local') {
            if ($this->isOffline()) {
                throw new DeviceNotConnectedException($this->type->name);
            }
        }

        $experiment = $this->getCurrentOrRequestedExperiment($softwareName);

        $deviceManager = new DeviceManager($this, $experiment);

        return $deviceManager->createDriver($this->type->name, $experiment->software->name);
    }

    public function getCurrentOrRequestedExperiment($softwareName = null)
    {
        // Get current running experiment
        $experiment = $this->currentExperiment;
        if (!is_null($experiment)) {
            return $experiment;
        }

        // If no experiment is running and no concrete software
        // implementation is requested, return default one
        if (is_null($softwareName)) {
            $experiment = $this->getDefaultExperiment();
            return $experiment;
        }

        // If concrete software implementation is requested
        // try to find it, otherwise return exception
        return $this->getExperimentBySoftwareName($softwareName);
    }

    public function getDefaultExperiment()
    {
        try {
            $experiment = $this->defaultExperiment()->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new DefaultExperimentNotFoundException($this);
        }

        return $experiment;
    }

    public function getExperimentBySoftwareName($softwareName)
    {
        $software = Software::where('name', $softwareName)->firstOrFail();

        try {
            $experiment = Experiment::where('software_id', $software->id)->where('device_id', $this->id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ExperimentNotSupportedException($softwareName);
        }
        return $experiment;
    }

    public function softwares()
    {
        return $this->belongsToMany(Software::class, "experiments");
    }

    public function experiments()
    {
        return $this->hasMany(Experiment::class);
    }

    public function defaultExperiment()
    {
        return $this->belongsTo(Experiment::class, "default_experiment_id");
    }

    public function getDefaultSoftwareAttribute()
    {
        return $this->defaultExperiment->software->name;
    }

    public function currentExperiment()
    {
        return $this->belongsTo(Experiment::class, "current_experiment_id");
    }

    public function currentExperimentLogger()
    {
        return $this->belongsTo(ExperimentLog::class, "current_experiment_log_id");
    }

    public function experimentLogs()
    {
        return $this->hasManyThrough(ExperimentLog::class, Experiment::class);
    }

    /**
     * Get current software type name
     * @return mixed [string|null]
     */
    public function currentSoftwareName()
    {
        $experiment = $this->currentExperiment;

        return is_null($experiment) ? null : $experiment->software->name;
    }

    public function detachCurrentExperiment()
    {
        $this->current_experiment_id = null;
        $this->current_experiment_log_id = null;
        $this->save();
    }

    public function attachPid($pid)
    {
        $this->fresh();
        $pids = json_decode($this->attached_pids);
        $pids []= $pid;
        $this->attached_pids = json_encode($pids);
        $this->save();
    }

    public function detachPids()
    {
        $this->attached_pids = null;
        $this->save();
    }

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    protected function isOffline()
    {
        return !File::exists($this->port);
    }

    public function getStatus()
    {
        if ($this->isOffline()) {
            $this->status = DeviceDriverContract::STATUS_OFFLINE;
            $this->save();
            return $this->status;
        }

        // Database says the device if offline, but reality is,
        // there is a something connected at port of this
        // device, so we better check it out, if status
        // is not outdated
        if ($this->status == DeviceDriverContract::STATUS_OFFLINE) {
            $driver = $this->driver();
            $this->status = $driver->status();
            $this->save();
        }

        return $this->status;
    }

    public function resetDevice()
    {
        // We can't really do anything with a device
        // if there is nothing connected at the
        // port this device has in DB
        if ($this->isOffline()) {
            throw new DeviceNotConnectedException;
        }

        // First things first - lets try to stop all
        // processes attached to this device
        $driver = $this->driver();
        $driver->forceStop();

        // Now let's query the physical device for its
        // status
        $this->status = $driver->status();
        $this->save();
    }
}
