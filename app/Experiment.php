<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    public function device() {
    	return $this->belongsTo(Device::class);
    }

    public function type() {
    	return $this->belongsTo(ExperimentType::class,"experiment_type_id");
    }

    public function getInputArguments() {
    	$deviceName = $this->device->type->name;
    	$experimentName = $this->type->name;

    	return $this->getInputFromConfig($deviceName, $experimentName);
    }

    public function getOutputArguments()
    {
    	$deviceName = $this->device->type->name;

    	return $this->getOutputFromConfig($deviceName);	
    }

    protected function getInputFromConfig($deviceName, $experimentName)
    {
    	return config(
    		'devices.'  . 
    		$deviceName . 
    		'.experiments.' .
    		$experimentName .
    		'.input'
    	);
    }

    protected function getOutputFromConfig($deviceName)
    {
    	return config(
    		'devices.'  . 
    		$deviceName . 
    		'.output'
    	);
    }
}
