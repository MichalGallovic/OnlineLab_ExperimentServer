<?php

namespace App\Classes\WebServer;

use App\Experiment;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

/**
*  Olm App server communication wrapper
*  Guzzle heavy use
*/
class Server
{
    /**
     * Server ip address
     * @var [type]
     */
    protected $ip;

    /**
     * Guzzle HTTP client
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Olm app server API prefix
     * @var [type]
     */
    protected $apiPrefix = "api";


    /**
     * Is server reachable
     * @var boolean
     */
    protected $reachable;

    /**
     * Last response code
     * @var [type]
     */
    protected $lastResponseCode;

    public function __construct($ip)
    {
        $this->ip = mb_substr($ip, -1) == "/" ? substr($ip, 0, count($ip) - 2) : $ip;

        $this->client = new Client([
            "base_uri"  =>  "http://" . $this->ip,
            "timeout"   =>  2.0 
        ]);
    }

     /**
     * Gets the Server ip address.
     *
     * @return [type]
     */
    public function getIp()
    {
        return $this->ip;
    }

    public function updateExperimentStatus(Experiment $experiment, $status)
    {
        return $this->postExperimentStatusUpdate($experiment, $status);
    }

    public function updateExperimentReport($output, $reportId)
    {
        return $this->postExperimentStatusUpdate($output, $reportId);   
    }

    protected function postExperimentReport($output, $reportId)
    {
        $input = [
            "report"  => $output
        ];

        $body = $this->post("report/" . $reportId,$input);
        return $body;
    }

    protected function postExperimentStatusUpdate($experiment, $status)
    {
        $input = [
            "device"        =>  $experiment->device->type->name,
            "software"      =>  $experiment->software->name,
            "device_name"   =>  $experiment->device->name,
            "status"        =>  $status
        ];

        $body = $this->post("experiments/status",$input);
        return $body;
    }

    protected function get($segments = null, $force = false)
    {
        $url = $this->prepareUrl($segments);

        $response = null;
        try {
            $response = $this->client->get($url);
            $this->lastResponseCode = $response->getStatusCode();
            $this->reachable = true;
            $response = $this->responseToArray($response);
        } catch(ConnectException $e) {
            $this->reachable = false;
        } catch(ClientException $e) {
            $this->lastResponseCode = $e->getResponse()->getStatusCode();
        }

        return $response;
    }

    protected function post($segments = null, $input = null, $force = false)
    {
        $url = $this->prepareUrl($segments);

        $response = null;
        try {
            $response = $this->client->request("POST",$url, [
                    "json" => $input
                ]);

            $this->lastResponseCode = $response->getStatusCode();
            $this->reachable = true;
            $response = $this->responseToArray($response);
        } catch(ConnectException $e) {
            $this->reachable = false;
        } catch(ClientException $e) {
            $this->lastResponseCode = $e->getResponse()->getStatusCode();
            $response = $this->responseToArray($e->getResponse());
        }

        return $response;
    }

    protected function prepareUrl($segments = null)
    {
        $segments = substr($segments,0,1) == "/" ? substr($segments, 1, count($segments) - 1) : $segments;
        return $this->apiPrefix . "/" . $segments;
    }

    protected function responseToArray($response)
    {
        return json_decode($response->getBody(), true);
    }
}