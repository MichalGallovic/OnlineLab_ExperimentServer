<?php

namespace App\Devices\Commands;

use App\Experiment;

/**
* Command base
*/
abstract class Command
{

	/**
	 * Command name
	 * @var string
	 */
	protected $name;

	abstract public function execute();
	abstract public function stop();

}