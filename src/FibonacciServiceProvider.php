<?php

namespace Kadevjo\Fibonacci;

use Illuminate\Support\ServiceProvider;

class FibonacciServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__."routes.php";
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
    
    public function provides() {
        return ['fibonacci'];
    }
}