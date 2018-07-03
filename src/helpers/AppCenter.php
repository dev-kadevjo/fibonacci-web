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

    public static function sendNotification($name, $title, $body,$devices_ios,$devices_droid,$custom_data = null)
    {
        $client = new \GuzzleHttp\Client();
        $baseUrl = "https://api.appcenter.ms/v0.1/apps/";
       
        $response_droid = $client->request('POST', $baseUrl.''.env('APPCENTER_OWNER').'/'.env('APPCENTER_APP_ANDROID').'/push/notifications',[
            'headers' =>['X-API-Token' => "41f2c57defc74a2a948f9c844cb5264698e32daf", 'Content-Type' => 'application/json'],
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
            
        $response_ios = $client->request('POST', $baseUrl.''.env('APPCENTER_OWNER').'/'.env('APPCENTER_APP_IOS').'/push/notifications',[
            'headers' =>[
                'X-API-Token' => "41f2c57defc74a2a948f9c844cb5264698e32daf",
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