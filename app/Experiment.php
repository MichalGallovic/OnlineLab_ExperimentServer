<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\ParametersInvalidException;

class Experiment extends Model
{
    public function device() {
    	return $this->belongsTo(Device::class);
    }

    public function software() {
    	return $this->belongsTo(Software::class);
    }

    public function getInputArguments($command = null) {
    	$deviceName = $this->device->type->name;
    	$softwareName = $this->software->name;

    	return $this->getInputFromConfig($deviceName, $softwareName, $command);
    }

    public function getInputArgumentsNames($command = null) {
        $inputArguments = $this->getInputArguments($command);

        if(is_null($inputArguments)) return null;

        $inputNames = [];

        if(is_null($command)) {
            foreach ($inputArguments as $commandName => $arguments) {
                $inputNames[$commandName] = [];
                foreach ($arguments as $argument) {
                    $inputNames[$commandName] []= $argument['name'];
                }
            }
        } else {
            foreach ($inputArguments as $argument) {
                $inputNames []= $argument['name'];
            }
        }

        return $inputNames;
    }

    public function getInputRules($command = null)
    {
        $inputArguments = $this->getInputArguments($command);

        if(is_null($inputArguments)) return null;

        $inputRules = [];

        if(is_null($command)) {
            foreach ($inputArguments as $commandName => $arguments) {
                $inputRules[$commandName] = [];
                foreach ($arguments as $argument) {
                    $inputRules[$commandName][$argument['name']] = $argument['rules'];
                }
            }
        } else {
            foreach ($inputArguments as $argument) 
            {
                $inputRules[$argument['name']] = $argument['rules'];
            }
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

    public function __call($method, $arguments) 
    {
        $availableCommands = DeviceDriverContract::AVAILABLE_COMMANDS;
        $command = str_replace("validate", "", $method);
        $command = Str::lower($command);
        if(in_array($command, $availableCommands)) {
            return $this->validate($arguments[0], $command);
        }

        return parent::__call($method, $arguments);
    }

    public function validate($input, $command) {
        if (!is_array($input)) {
            $arguments = array_keys($input);
            $arguments = implode(" ,", $arguments);
            throw new ParametersInvalidException("Wrong input arguments, expected: [" . $arguments . "]");
        }

        $rules = $this->getInputRules($command);
        $rules = is_null($rules) ? [] : $rules;

        $validator = Validator::make($input, $rules);

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

    protected function getInputFromConfig($deviceName, $softwareName, $command)
    {
        $command = !is_null($command) ? ".$command" : ""; 

    	return config(
    		'devices.'  . 
    		"$deviceName." .
    		"$softwareName." .
            'input' .
            $command
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
