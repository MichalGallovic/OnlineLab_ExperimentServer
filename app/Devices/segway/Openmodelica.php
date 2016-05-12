<?php

namespace App\Devices\segway;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

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
        "read" => "",
        "stop" => "",
        "start" => "",
        "init" => "",
        "change" => ""
    ];
    protected $client;
    protected $uploaded_files_dir = "";
    protected $password = "";
   // protected $T_sim = 60;

    /**
     * Construct base class (App\Devices\AbstractDevice)
     * @param Device     $device     Device model from DB
     * @param Experiment $experiment Experiment model from DB
     */
    public function __construct(Device $device, Experiment $experiment) {

        //require_once('../Helpers/WSocketServer.php');
        $this->client = new Client("ws://127.0.0.1:18000");
        
        require '../../html/laboratory/WebServer/MSconfig.php';
        
        $this->password=$config['Passprahses']['olm']; 
        
        parent::__construct($device, $experiment);
    }

    protected function init($input) {
      //  var_dump($input); die();
      //  $vars['T_sim'] = $input['cas_sim'];

        $vars['servo_taz'] = $input['servo_taz'];
        if (!empty($input['libraries'])) $vars['libraries'] = trim($input['libraries']);
        //$this->T_sim = $input['cas_sim'];

        switch ($input['reg_typ']) {
            case "PID":
                $vars['inputtype'] = 'equations';

                if (((float) $input['PID_Ti']) == 0)
                    $input['PID_Ti'] = 100000000000;

                $vars['equations']['controller'] = 'e=setpoint_phi - phi; '
                        . 'der(ie)=e; '
                        . ' actuator_value = (-e * P) +(-ie*P/Ti ) -(P*Td*dphi); ';


                $vars['equations']['variables'] = 'Real e(start=0) "control error"; '
                        . 'Real ie(start=0) "integrated control error"; ';


                $vars['equations']['parameters'] = 'parameter Real P=' . (float) $input['PID_P'] . ' "proportional gain P"; '
                        . 'parameter Real Ti=' . (float) $input['PID_Ti'] . ' "integral time constant Ti"; '
                        . 'parameter Real Td=' . (float) $input['PID_Td'] . ' "derivative time constant Td"; ';

                break;
            case "Rovnice":
                $vars['inputtype'] = 'equations';

                $vars['equations']['controller'] = $input['equations_controller'];
                $vars['equations']['variables'] = $input['equations_variables'];
                $vars['equations']['parameters'] = $input['equations_parameters'];

                break;
            case "Súbor":
                $vars['inputtype'] = 'file';
                $vars['file']['path'] = $input['file_schema'];
                break;
            default:
                return 'error';
                break;
        }
        $vars = json_encode($vars);

        $this->client->send("#".$this->password."#init_sim:" . $vars);

        $response = " ";
        $cnt = 0;
        $this->client->setTimeout(500);

        while ((strpos($response, "init:end") === false) && $cnt < 1) {
            try {
                $response = $this->client->receive();
            } catch (\Exception $exc) {
                $mess = $exc->getMessage();
                if (strpos($mess, "Empty read; connection dead?") === false) {
                    echo $exc->getMessage();
                } else {//no message received in timeout
                }
            }
            $cnt++;
        }
        $this->client->setTimeout(10);
        if (strpos($response, "init:end") === false) {
            return $response;
        } else {
            return $response;
        }
    }

    protected function start($input) {
        
        $input["s_rate"]=$input["s_rate"]/1000; //from miliseconds to seconds
        
        chmod($this->experimentLog->output_path,0664);
        $response = " ";
        //$input['cas_sim']
        //       while ((strpos($mess, "sim:stop_sent") === false)) {
        $input['output_path']=  $this->experimentLog->output_path;
        //var_dump($input);die();
        $this->client->send("#".$this->password."#start_sim:" . json_encode($input));
        // $this->client->setTimeout($this->T_sim+10);
        $this->client->setTimeout(15);
        try {
            sleep(1);
            $response = $this->client->receive();
        } catch (\Exception $exc) {
            $mess = $exc->getMessage();
            if (strpos($mess, "Empty read; connection dead?") === false) {
                echo $exc->getMessage();
            } else {//no message received in timeout
            }
        }


        if (strpos($response, "simulation is not ready") === false) {
            
        }else {
            
            $this->client->setTimeout(5);
            return "simulation is beeing initialized";
        }

        $response = " ";
        $cnt = 0;
        
        while ((strpos($response, "state:x") === false) && $cnt < ($input['cas_sim'])+5) {

            $this->client->send("#".$this->password."#state_sim");
            sleep(1);
            $response = $this->client->receive();

            
        }
            if (strpos($response, "state:x") === false) {
                return "OK";
            }
        return "message timeout";
    }

    // These methods have to be implemented
    // only if you are implementing
    // START command
    protected function parseDuration($input) {
        if ($input["cas_sim"] > 60) {
            $input["cas_sim"] = 60;
        }
        if ($input["cas_sim"] < 1) {
            $input["cas_sim"] = 1;
        }
        return $input['cas_sim'];
    }

    protected function parseSamplingRate($input) {
        if ($input["s_rate"] < 10) {
            $input["s_rate"] = 10;
        }
        return $input["s_rate"];
    }

    protected function stop($input) {
        $response = " ";
        //       while ((strpos($mess, "sim:stop_sent") === false)) {

        $this->client->send("#".$this->password."#stop_sim");

        try {
            sleep(1);
            $response = $this->client->receive();
        } catch (\Exception $exc) {
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

    //done
    protected function read($input) {

        $this->client->send("#".$this->password."#read_data");

        $response = " ";
        $cnt = 0;
        while ((strpos($response, "phi") === false) && $cnt < 20) {
            try {
                $response = $this->client->receive();
            } catch (\Exception $exc) {
                $mess = $exc->getMessage();
                if (strpos($mess, "Empty read; connection dead?") === false) {
                    ///echo $exc->getMessage();
                } else {//no message received in timeout
                }
            }
            $cnt++;
        }



        //    var_dump($response); die();
        if (strpos($response, "phi") === false) {
            return;
        } else {
            $encoder_ticsperrotation = 1632.67;
            $polomer = 3;

            $jresponse = json_decode($response, true);
            $values['phi'] = $jresponse['phi'];
            $values['velocity_L'] = $jresponse['Ivelocity_L'] * 2 * 3.14159265359 * $polomer / $encoder_ticsperrotation;
            $values['velocity_R'] = $jresponse['Ivelocity_R'] * 2 * 3.14159265359 * $polomer / $encoder_ticsperrotation;
            $values['Avelocity_L'] = $jresponse['Ivelocity_L'] * 360.0 / $encoder_ticsperrotation;
            $values['Avelocity_R'] = $jresponse['Ivelocity_R'] * 360.0 / $encoder_ticsperrotation;
            $values['distance_L'] = $jresponse['Idistance_L'] * 2 * 3.14159265359 * $polomer / $encoder_ticsperrotation;
            $values['distance_R'] = $jresponse['Idistance_R'] * 2 * 3.14159265359 * $polomer / $encoder_ticsperrotation;
            $values['angle_L'] = $jresponse['Idistance_L'] * 360.0 / $encoder_ticsperrotation;
            $values['angle_R'] = $jresponse['Idistance_R'] * 360.0 / $encoder_ticsperrotation;
            $values['servo_tazisko'] = $jresponse['Servo_2'];
            return $values;
        }
    }

    //done
    protected function change($input) {
        

        $this->client->send("#".$this->password."#change_refVal:" . json_encode($input));

        $response = " ";
        $cnt = 0;
        while ((strpos($response, "Refval changed") === false) && $cnt < 5) {

            try {

                $response = $this->client->receive();
            } catch (\Exception $exc) {


                $mess = $exc->getMessage();


                if (strpos($mess, "Empty read; connection dead?") === false) {
                    echo $exc->getMessage();
                } else {//no message received in timeout
                }
            }
            $cnt++;
        }

        //die();
        if (strpos($response, "Refval changed") === false) {
            return "Try again please";
        } else {
            return "OK";
        }
    }

    /* protected function status($input) {

      $this->client->send("#".$this->password."#state_sim");

      $response = " ";
      $cnt = 0;
      while ((strpos($response, "status:") === false) && $cnt < 2) {
      try {
      $response = $this->client->receive();
      } catch (\Exception $exc) {
      $mess = $exc->getMessage();
      if (strpos($mess, "Empty read; connection dead?") === false) {
      echo $exc->getMessage();
      } else {//no message received in timeout
      }
      }
      $cnt++;
      }

      if (strpos($response, "state:") === false) {
      return "Try again please";
      } else {

      $state['status']='ready';
      return $state;
      }
      } */
}
