<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Devices\Exceptions\ParametersInvalidException;

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

    public function getInputArgumentsNames() {
        $arguments = $this->getInputArguments();

        $inputNames = [];

        foreach ($arguments as $argument) {
            $inputNames []= $argument['name'];
        }

        return $inputNames;
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

    public function getOutputArgumentsTitles() {
        $deviceName = $this->device->type->name;

        $configOutput = $this->getOutputFromConfig($deviceName);

        return $this->parseOutputTitles($configOutput);
    }

    public function getOutputArgumentsAll() {
        $deviceName = $this->device->type->name;

        return $this->getOutputFromConfig($deviceName);
    }

    public function getOutputArguments()
    {
    	$deviceName = $this->device->type->name;

        $configOutput = $this->getOutputFromConfig($deviceName);

    	return $this->parseOutputNames($configOutput);
    }

    public function validate($input) {
        if (!is_array($input)) {
            $arguments = array_keys($input);
            $arguments = implode(" ,", $arguments);
            throw new ParametersInvalidException("Wrong input arguments, expected: [" . $arguments . "]");
        }

        $validator = Validator::make($input, $this->getInputRules());

        if ($validator->fails()) {
            throw new ParametersInvalidException($validator->messages());
        }
    }

    protected function parseOutputTitles($output) {
        $output = array_map(function($item) {
            return $item['title'];
        }, $output);

        return $output;
    }

    protected function parseOutputNames($output) {
        $output = array_map(function($item) {
            return $item['name'];
        }, $output);

        return $output;
    }

    protected function getInputFromConfig($deviceName, $softwareName)
    {
    	return config(
    		'devices.'  . 
    		$deviceName . 
    		'.input.' .
    		$softwareName
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
