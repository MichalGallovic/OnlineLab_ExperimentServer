<?php

namespace App\Devices\Scripts\Exceptions;

class ScriptDoesNotExistException extends \Exception
{
	

	public function __construct($paths)
	{
		$message = "Scripts at: \n";

		foreach ($paths as $index => $path) {
			$last = end($paths);

			if($path != $last) {
				$message .= "\"" . $path . "\"\n or \n";
			} else {
				$message .= "\"" . $path . "\"\n";
			}

		}

		$message .= "does not exist!";

		parent::__construct($message);
	}


}