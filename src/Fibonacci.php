<?php

namespace Kadevjo\Fibonacci;

use Illuminate\Http\Request;
use Kadevjo\Fibonacci\Helpers\FacebookAccount;
use Kadevjo\Fibonacci\Helpers\OutlookAccount;
use Kadevjo\Fibonacci\Helpers\TwitterAccount;

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

        switch( strtolower($provider))
        {
            case self::FACEBOOK:
                $result = FacebookAccount::Auth($socialID, $data);
                break;
            case self::TWITTER:
                $result =  TwitterAccount::Auth($socialID, $data);
                break;
            case self::OUTLOOK:
                $result =  OutlookAccount::Auth($socialID, $data);
                break;
            default:
                break;
        }

        return $result;
    }
}