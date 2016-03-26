<?php

namespace App\Devices\Commands;

use App\Experiment;
use App\Devices\Commands\Command;
use App\Devices\Scripts\ReadScript;

class ReadCommand extends Command
{

	/**
	 * Reading script
	 * @var App\Devices\Scripts\ReadScript
	 */
	protected $script;

	/**
	 * Experiment DB
	 * @var App\Experiment
	 */
	protected $experiment;

	/**
	 * Formatted output from physical device
	 * @var array
	 */
	protected $output;

	public function __construct(Experiment $experiment, ReadScript $script)
	{
		$this->experiment = $experiment;
		$this->script = $script;
	}

	public function execute()
	{
		$this->script->run();
		$this->output = $this->combineOutputWithArguments(
			$this->script->getOutput(),
			$this->experiment->getOutputArguments()
		);
	}

	public function stop()
	{

	}

	/**
     * Tries to combine output array with arguments array
     * assigns it to the $this->output var
     * @param  array $output    Parsed device output
     * @param  array $arguments Device output arguments
     */
    protected function combineOutputWithArguments($output, $arguments)
    {
    	$combinedOutput = null;
        try {
            $combinedOutput = array_combine($arguments, $output);
        } catch (\Exception $e) {

        }
        return $combinedOutput;
    }

    /**
     * Gets the Formatted output from physical device.
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }
}