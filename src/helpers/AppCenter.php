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

        dd($credencials);
        $client = new \GuzzleHttp\Client();
        $baseUrl = "https://api.appcenter.ms/v0.1/apps/";
       
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

        return ["android" => json_decode($response_droid->getBody(),true),"ios" => json_decode($response_ios->getBody(),true)];
    }

}