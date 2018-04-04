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
        $this->app->singleton('fibonacci', function () {
            return new Fibonacci();
        });

        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
    }
    
    public function provides() {
        return ['fibonacci'];
    }

    private function registerConsoleCommands()
    {
        $this->commands(Commands\InstallCommand::class);
    }
}