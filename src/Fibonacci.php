<?php

namespace Kadevjo\Fibonacci;

use Illuminate\Http\Request;
use Kadevjo\Fibonacci\Helpers\SocialAccount;

class Fibonacci
{
    const FACEBOOK = "facebook";
    const TWITTER = "twitter";
    const OUTLOOK = "outlook";

    public function webRoutes()
    {
        require __DIR__.'/routes/web.php';
    }

    public function apiRoutes()
    {
        require __DIR__.'/routes/api.php';
    }

    public static function authenticateSocial($provider, $socialID, array $data)
    {
        $result = array();

        switch($provider)
        {
            case self::FACEBOOK:
                $result = SocialAccount::facebookAuth($socialID, $data);
                break;
            case self::TWITTER:
                $result =  SocialAccount::twitterAuth($socialID, $data);
                break;
            case self::OUTLOOK:
                $result =  SocialAccount::outlookAuth($socialID, $data);
                break;
            default:
                break;
        }

        return $result;
    }
}