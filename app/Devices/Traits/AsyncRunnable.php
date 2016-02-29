<?php

namespace App\Devices\Traits;

trait AsyncRunnable {

	public function run($input, $requestedBy) {
		parent::run($input, $requestedBy);
		
		$experimentProcess =  $this->runExperimentAsync($input);
		
		$this->waitOrTimeoutAsync($experimentProcess, $this->maxRunningTime);
		
		return $this->stop();
	
	}

}