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
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fibonacci');
        $this->loadViewsFrom(__DIR__.'/views', 'fibonacci');
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
            $this->registerPublishables();
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
    
    private function registerPublishables()
    {
        $publishablePath = dirname(__DIR__).'/src/resources';
        $path = ["{$publishablePath}/lang/" => base_path('resources/lang/')];
        $this->publishes($path,'lang');

        $publishablePath = dirname(__DIR__).'/src/config';
        $path = ["{$publishablePath}/fibonacci.php" => config_path('fibonacci.php')];
        $this->publishes($path,'config');
    }
}