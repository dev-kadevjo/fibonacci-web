<?php

namespace Kadevjo\Fibonacci\Traits;

use Kadevjo\Fibonacci\Observers\BaseObserver;

trait Loggable
{
    public static function bootLoggable()
    {
        static::observe(BaseObserver::class);
    }
    
}