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

    public static function sendNotification($name, $title, $body,$custom_data = null,$devices)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $baseUrl.'/'.env('APPCENTER_OWNER').'/'.env('APPCENTER_APP').'/push/notifications',[
            'header' =>['X-API-Token' => env('APPCENTER_TOKEN')],
            'body' => [
                'notification_content' => [
                    'name' => $name,
                    'title' => $title,
                    'body' => $body,
                    'custom_data' => $custom_data
                ],
                'notification_target' => [
                    'type' => 'devices_target',
                    'devices' => $devices
                ]
            ],
        ]);
        return json_decode($response->getBody(),true);
    }
}
