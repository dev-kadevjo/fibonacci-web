<?php

namespace Kadevjo\Fibonacci\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;

class OutlookAccount
{
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $picture;
    public $account;

    public static function Auth($userId, $token)
    {
        if( !array_key_exists('access_token',$token) ) throw new ConfigException("An error ocurred");
        $newRequest = "https://graph.microsoft.com/v1.0/me";
        $client = new Client(['headers' => ['Authorization' => 'Bearer '.$token['access_token']]]);
        $response = $client->request('GET',$newRequest);
        $content = json_decode($response->getBody()->getContents());
        $email = $content->userPrincipalName;
        $first_name = $content->givenName;
        $last_name = $content->surname;

        return json_decode(json_encode(array('email'=>$email, 'picture'=>null, 'first_name'=>$first_name,'last_name'=>$last_name)), FALSE);
    }
}
