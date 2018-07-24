<?php

namespace Kadevjo\Fibonacci\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class AppCenter
{
    private $baseUrl = "https://api.appcenter.ms/v0.1/apps/";

    public static function sendNotification($name, $title, $body,$devices_ios,$devices_droid,$class_name,$custom_data = null)
    {
        $credencials =  config('fibonacci.appcenter.'.$class_name);
        if(!$credencials)
            return ["error" => "invalid credencials"];
        $client = new \GuzzleHttp\Client();
        $baseUrl = "https://api.appcenter.ms/v0.1/apps/";
        if(count($devices_droid)>0)
        $response_droid = $client->request('POST', $baseUrl.$credencials['owner'].'/'.$credencials['droid'].'/push/notifications',[
            'headers' =>['X-API-Token' => $credencials['token'], 'Content-Type' => 'application/json'],
            'body' => json_encode([
                'notification_content' => [
                    'name' => $name,
                    'title' => $title,
                    'body' => $body,
                    'custom_data' => $custom_data
                ],
                'notification_target' => [
                    'type' => 'devices_target',
                    'devices' => $devices_droid
                ]
            ]),
        ]);
        if(count($devices_ios)>0)
         $response_ios = $client->request('POST', $baseUrl.$credencials['owner'].'/'.$credencials['ios'].'/push/notifications',[
                'headers' =>[
                    'X-API-Token' => $credencials['token'],
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'notification_content' => [
                        'name' => $name,
                        'title' => $title,
                        'body' => $body,
                        'custom_data' => $custom_data
                    ],
                    'notification_target' => [
                        'type' => 'devices_target',
                        'devices' => $devices_ios
                    ]
                ]),
            ]);
        if(count($devices_ios)>0 && count($devices_droid)>0 ){
            return ["android" => json_decode($response_droid->getBody(),true),"ios" => json_decode($response_ios->getBody(),true)];
        }elseif(count($devices_droid)>0){
            return ["android" => json_decode($response_droid->getBody(),true),"ios" => ""];
        }
        elseif(count($devices_ios)>0){
            return ["android" => "","ios" => json_decode($response_ios->getBody(),true)];
        }else{
            return ["android" => "","ios" => ""];
        }
       
    }
}