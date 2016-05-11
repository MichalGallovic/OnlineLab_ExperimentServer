<?php

namespace App\Classes\Services;

use App\ExperimentLog;
use Illuminate\Support\Facades\DB;

/**
*  System Status Service
*/
class SystemStatusService
{

	protected $database;
	protected $redis;
	protected $queue;

	public function __construct()
	{
		$this->database = false;	
		$this->redis = false;
		$this->queue = false;	
	}

	public function check()
	{
		$this->checkRedis();
		$this->checkQueue();
		$this->checkDatabase();
	}

	public function response()
	{
		return [
			"database" => $this->database,
			"redis"	=>	$this->redis,
			"queue"	=>	$this->queue
		];
	}

	protected function checkRedis()
	{
		$this->redis = true;
	}

	protected function checkQueue()
	{
		$this->queue = true;
	}

	protected function checkDatabase()
	{
		try {
			// Pingping whatever table to check connection
			ExperimentLog::first();
			$this->database = true;
		} catch(\Exception $e) {
			$this->database = false;
		}
	}
}