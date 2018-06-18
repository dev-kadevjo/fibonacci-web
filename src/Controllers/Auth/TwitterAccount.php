<?php

namespace Kadevjo\Fibonacci\Controllers\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TwitterAccount
{
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $picture;
    public $account;

    public static function Auth($userId, $token)
    {
        if( !array_key_exists('oauth_token',$token) || !array_key_exists('oauth_token_secret',$token) ) throw new ConfigException("An error ocurred");

        $newRequest = "account/verify_credentials.json?include_email=true";

        $stack = HandlerStack::create();
        if( is_null(config('fibonacci.auth-social.providers.twitter.consumer_key'))
        || is_null(config('fibonacci.auth-social.providers.twitter.consumer_secret'))
         ) throw new ConfigException("An error ocurred");

        $middleware = new Oauth1([
            'consumer_key'    => config('fibonacci.auth-social.providers.twitter.consumer_key'),
            'consumer_secret' => config('fibonacci.auth-social.providers.twitter.consumer_secret'),
            'token'           => $token['oauth_token'],
            'token_secret'    => $token['oauth_token_secret']
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack
        ]);

        // Set the "auth" request option to "oauth" to sign using oauth
        $response = $client->get($newRequest, ['auth' => 'oauth']);
        $content = json_decode($response->getBody()->getContents());
        $email = $content->email;
        $picture = str_replace("_normal.", ".", $content->profile_image_url_https);
        $first_name = $content->name;

        return json_decode(json_encode(array('email'=>$email, 'picture'=>$picture, 'first_name'=>$first_name,'last_name'=>null)), FALSE);

    }
}
