<?php

namespace Kadevjo\Fibonacci\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;
use Illuminate\Support\Arr;

class GoogleAccount
{
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $picture;
    public $account;

    public static function Auth($userId, $token)
    {
        if( !$token ) throw new ConfigException("An error ocurred");
        
        
        $client = new Client();
        $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo?', [
            'query' => [
                'prettyPrint' => 'false',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],]);
           
            
        $response =  json_decode($res->getBody(), true);
        $email = Arr::get($response, 'email');
        $first_name = Arr::get($response, 'name');
        $last_name = Arr::get($response, 'name');
        $picture =Arr::get($response, 'picture');
        $backend = json_decode(json_encode(array('email'=>$email, 'picture'=>$picture, 'first_name'=>$first_name,'last_name'=>null)), FALSE);        
        return $backend;
    }

}
