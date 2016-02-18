<?php 

namespace App\Devices\Exceptions;

use App\Classes\Traits\ApiRespondable;

class DriverDoesNotExistException extends \Exception
{
	use ApiRespondable;

	public function getResponse() {
		return $this->errorNotFound("Driver does not exist");
	}
	
}