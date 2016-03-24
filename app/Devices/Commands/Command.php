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

	//@Todo someday todo, maybe this could also serve as a factory
	//that can produce specific commands upon ::create ?
}