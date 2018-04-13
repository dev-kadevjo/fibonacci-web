<?php 

namespace Kadevjo\Fibonacci\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Psr7;
use Kadevjo\Fibonacci\Exceptions\ConfigException;

class SocialAccount
{
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $picture;
    public $account;
    
    public static function facebookAuth($userId, $token)
    {
        if( !array_key_exists('access_token',$token) ) throw new ConfigException("An error ocurred");
        $resource = "https://graph.facebook.com/v2.8/".$userId."?fields=email,first_name,last_name,picture";
        $path = $resource."&access_token=".$token['access_token'];
        $client = new Client(['base_uri' => 'https://foo.com/api/']);
        $request = $client->get($path);
        $response = json_decode($request->getBody()->getContents());
        $email = $response->email;
        $first_name = $response->first_name;
        $last_name = $response->last_name;
        $picture = "https://graph.facebook.com/v2.8/".$userId."/picture?type=large";
        return json_decode(json_encode(array('email'=>$email, 'picture'=>$picture, 'first_name'=>$first_name,'last_name'=>$last_name)), FALSE);
    }

    public static function twitterAuth($userId, $token)
    {
        if( !array_key_exists('oauth_token',$token) || !array_key_exists('oauth_token_secret',$token) ) throw new ConfigException("An error ocurred");

        $newRequest = "account/verify_credentials.json/include_email=true";

        $stack = HandlerStack::create();

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
        $first_name = $content->first_name;
        $last_name = $content->last_name;
        return json_decode(json_encode(array('email'=>$email, 'picture'=>$picture, 'first_name'=>$first_name,'last_name'=>$last_name)), FALSE);

    }

    public static function outlookAuth($userId, $token)
    {
        if( !array_key_exists('access_token',$token) ) throw new ConfigException("An error ocurred");        
        
        $newRequest = "https://outlook.office.com/api/v2.0/me";
        $client = new Client(['headers' => ['Authorization' => 'Bearer '.$token['access_token']]]);
        $response = $client->request('GET',$newRequest);
        $content = json_decode($response->getBody()->getContents());
        $email = $content->EmailAddress;
        $username = $content->DisplayName;
        return json_decode(json_encode(array('email'=>$email, 'picture'=>null, 'username'=>$username)), FALSE);
        
    }
}