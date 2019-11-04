<?php

namespace Kadevjo\Fibonacci;

use Illuminate\Http\Request;
use Kadevjo\Fibonacci\Helpers\FacebookAccount;
use Kadevjo\Fibonacci\Helpers\OutlookAccount;
use Kadevjo\Fibonacci\Helpers\TwitterAccount;
use Kadevjo\Fibonacci\Helpers\GoogleAccount;

class Fibonacci
{
    const FACEBOOK = "facebook";
    const TWITTER = "twitter";
    const OUTLOOK = "outlook";
    const GOOGLE = "google";

    public function webRoutes()
    {
        require __DIR__.'/routes/web.php';
    }

    public function apiRoutes()
    {
        require __DIR__.'/routes/api.php';
    }

    public static function authenticateSocial($provider, $socialID, $data)
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
            case self::GOOGLE:
                $result =  GoogleAccount::Auth($socialID, $data);
                break;
            default:
                break;
        }

        return $result;
    }
}