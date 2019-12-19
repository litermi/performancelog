<?php

namespace Litermi\performanceLog\Middleware;

use Closure;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions as RequestOptions;

class PerformanceLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        //check if defined configuration for elasticsearch
        $elastic_server  = config('elastic.elastic_url').':'.config('elastic.elastic_port');
        if (isset($elastic_server)) {
            //get configuration from env
            $env = config('app.env');
            //get elastic timezone
            $elastic_tz  = config('elastic.elastic_timezone');
            //messure the time and convert to ms
            $execution_time = round((microtime(true) - LARAVEL_START) * 1000);
            //create DateTime Object
            $date_time = new \DateTime();
            //get date from object
            $date = $date_time->format('Y-m-d');
            //transform to elastic date format
            $date_time = str_replace(" ", "T", $date_time->format('Y-m-d H:i:s'));
            //Add timezone
            $date_time .= $elastic_tz;
            //get method and endpoint from request
            $endpoint = $request->method() . ' ' . $request->path();
            $endpoint_general = "";

            $fragments = explode("/", $endpoint);
            if (is_numeric(end($fragments))) {
                $endpoint_general =  str_replace(end($fragments), "", $endpoint);
            } else {
                $endpoint_general = $endpoint;
            }
            //create access token
            $base = config('elastic.elastic_user').':'.config('elastic.elastic_pass');
            $token ='Basic '. Base64_encode($base); 

            //create url for index
            $elasticURL = $elastic_server . '/'.config('app.env').'-' . $env . '-performance-' . $date . '/performance-log/' . time();
            $headers = [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'authorization' => $token,
            ];
            //Disable SSL verification for elastic server
            $client = new ClientHttp(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));

            $payload = ['date' => $date_time, 'response_time' => $execution_time, 'endpoint' => $endpoint, 'endpoint_generic' => $endpoint_general];



            try {
                $response = $client->post($elasticURL, [
                    'headers' => $headers,
                    RequestOptions::JSON => $payload
                ]);
            } catch (\Exception $e) {
                //log error
            }
        }
    }
}

