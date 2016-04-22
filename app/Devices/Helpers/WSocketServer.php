<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('libWS/Exception.php');
require_once('libWS/BadOpcodeException.php');
require_once('libWS/BadUriException.php');
require_once('libWS/ConnectionException.php');
require_once('libWS/Base.php');
require_once('libWS/Client.php');


//require_once('libWS/Server.php');

//echo "caua"; die();





//$client = new Client("ws://:18000");
//$client->send("#state_omc");

//sleep(1);
//try {
//    echo $client->receive();
//} catch (Exception $exc) {
//    $mess=$exc->getMessage();
//    if (strpos($mess, "Empty read; connection dead?")===false){
//        echo $exc->getMessage();
//    } else {//no message received in timeout
//        echo "its ok bro";
//    }
//    
//}
//$client->send("#state_daemon");
//sleep(5);
//echo $client->receive();

