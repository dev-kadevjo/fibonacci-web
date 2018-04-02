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
        
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Kadevjo\Fibonacci\Controllers\APIController');
        $this->app->make('Kadevjo\Fibonacci\Controllers\DatabaseController');
    }
    
    public function provides() {
        return ['fibonacci'];
    }
}