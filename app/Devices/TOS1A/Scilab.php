<?php namespace App\Devices\TOS1A;

use App\Devices\AbstractDevice;
use App\Devices\Contracts\DeviceDriverContract;

class Scilab extends AbstractDevice
{
	public function __construct($device,$experiment) 
	{
		parent::__construct($device,$experiment);
	}

	protected function start($input)
	{
		// input obsahuje vstupne argumenty pre sustavu
		
	}
}