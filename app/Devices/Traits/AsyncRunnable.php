<?php

namespace App\Devices\Traits;

trait AsyncRunnable {

	public function run($input) {
		parent::run($input);
		
		$experimentProcess =  $this->runExperimentAsync($input);
		
		$this->waitOrTimeoutAsync($experimentProcess, $this->maxRunningTime);
		
		return $this->stop();
	
	}

}