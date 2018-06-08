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

        \Config::set('auth.guards.api',     ['driver'   => 'jwt','provider' => 'clients']);
        \Config::set('auth.providers.clients',  ['driver'   => 'eloquent','model'   => \Kadevjo\Fibonacci\Models\Client::class]);
    }

    public function provides() {
        return ['fibonacci'];
    }

    private function registerConsoleCommands()
    {
        $this->commands(Commands\InstallCommand::class);
    }

    private function registerPublishables(){
        $publishablePath = dirname(__DIR__).'/src/publishable';
        $publishable = [
            'reports_assets' => [
                "{$publishablePath}/css/reports.css" => public_path('css/reports.css'),
                "{$publishablePath}/js/reports.js" => public_path('js/reports.js'),
            ],
            'config' => [
            "{$publishablePath}/config/fibonacci.php" => config_path('fibonacci.php'),
            ],
            'lang' => [
            "{$publishablePath}/lang/" => base_path('resources/lang/'),
            ],
        ];
        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
