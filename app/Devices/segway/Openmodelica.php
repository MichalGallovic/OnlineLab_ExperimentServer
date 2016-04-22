<?php


namespace App\Devices\segway;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Helpers\WSocketServer;

require_once('Exception.php');
require_once('BadOpcodeException.php');
require_once('BadUriException.php');
require_once('ConnectionException.php');
require_once('Base.php');
require_once('Client.php');
use WebSocket\Client;
        
       
class Openmodelica extends AbstractDevice implements DeviceDriverContract {

    /**
     * Paths to read/stop/run scripts relative to
     * $(app_root)/server_scripts folder
     * @var array
     */
    protected $scriptPaths = [
        "read" => "a",
        "stop" => "a",
        "start" => "a",
        "init" => "a",
        "change" => "a"
    ];
    protected $client;

    /**
     * Construct base class (App\Devices\AbstractDevice)
     * @param Device     $device     Device model from DB
     * @param Experiment $experiment Experiment model from DB
     */
    public function __construct(Device $device, Experiment $experiment) {
        
        //require_once('../Helpers/WSocketServer.php');
        $this->client=new Client("ws://127.0.0.1:18000");
        parent::__construct($device, $experiment);
        
    }

    protected function init($input) {
        
    }
	protected function start($input)
	{
		$script = new StartScript(
			$this->scriptPaths["start"],
			$input,
			$this->device,
			$this->experimentLog->output_path
			);

		$script->run();

	}
    // These methods have to be implemented
    // only if you are implementing
    // START command
    protected function parseDuration($input) {
        return $input["t_sim"];
    }

    protected function parseSamplingRate($input) {
        return $input["s_rate"];
    }

    protected function stop($input) {
        $response = " ";
        //       while ((strpos($mess, "sim:stop_sent") === false)) {

        $this->client->send("#stop_sim");

        try {
            sleep(1);
            $response = $this->client->receive();
        } catch (Exception $exc) {
            $mess = $exc->getMessage();
            if (strpos($mess, "Empty read; connection dead?") === false) {
                echo $exc->getMessage();
            } else {//no message received in timeout
            }
        }
        
        if (strpos($response, "Simulation is being stopped") === false) {
            return "Try again please";
        } else {
            return $response;
        }
    }

}
