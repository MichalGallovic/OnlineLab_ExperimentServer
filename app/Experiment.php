<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    public function device() {
    	return $this->belongsTo(Device::class);
    }

    public function software() {
    	return $this->belongsTo(Software::class);
    }

    public function getInputArguments() {
    	$deviceName = $this->device->type->name;
    	$softwareName = $this->software->name;

    	return $this->getInputFromConfig($deviceName, $softwareName);
    }

    public function getOutputArguments()
    {
    	$deviceName = $this->device->type->name;

    	return $this->getOutputFromConfig($deviceName);
    }

    public function getInputRules()
    {
    	$inputArguments = $this->getInputArguments();

    	$inputRules = [];

    	foreach ($inputArguments as $argument) 
    	{
    		$inputRules[$argument['name']] = $argument['rules'];
    	}

    	return $inputRules;

    }

    protected function getInputFromConfig($deviceName, $softwareName)
    {
    	return config(
    		'devices.'  . 
    		$deviceName . 
    		'.experiments.' .
    		$softwareName .
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
