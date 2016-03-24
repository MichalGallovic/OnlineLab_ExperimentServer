<?php

namespace App\Devices\Scripts;

use App\Device;
use App\Software;
use Illuminate\Support\Facades\File;
use App\Devices\Scripts\Exceptions\ScriptDoesNotExistException;

/**
* Base Script representation
*/
class Script
{
    /**
     * Path to script
     * @var string
     */
    protected $path;

    public function __construct($path)
    {
    	$absolutePath = $this->basePath() . "/" . $path;

    	if(File::exists($absolutePath)) {
    		$this->path = $absolutePath;
    	} else if(File::exists($path)) {
    		$this->path = $path;
    	} else {
    		throw new ScriptDoesNotExistException([$absolutePath, $path]);
    	}
    }

    public function setPath($path)
    {
    	$this->path = $path;
    }

    public function getPath()
    {
    	return $this->path;
    }

    public function basePath()
    {
    	return base_path("server_scripts");
    }
}
