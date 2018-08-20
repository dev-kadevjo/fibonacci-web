<?php

namespace Kadevjo\Fibonacci\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;

class FacebookAccount
{
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $picture;
    public $account;

    public static function Auth($userId, $token)
    {
        if( !$token ) throw new ConfigException("An error ocurred");
        $resource = "https://graph.facebook.com/v2.8/".$userId."?fields=email,first_name,last_name,picture";
        $path = $resource."&access_token=".$token;
        $client = new Client(['base_uri' => 'https://foo.com/api/']);
        $request = $client->get($path);
        $response = json_decode($request->getBody()->getContents());
        $email = $response->email;
        $first_name = $response->first_name;
        $last_name = $response->last_name;
        $picture = "https://graph.facebook.com/v2.8/".$userId."/picture?type=large";
        return json_decode(json_encode(array('email'=>$email, 'picture'=>$picture, 'first_name'=>$first_name,'last_name'=>$last_name)), FALSE);
    }

}
