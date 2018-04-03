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
    public function boot(Filesystem $filesystem)
    {
        $routes_contents = $filesystem->get(base_path('routes/web.php'));
        if (false === strpos($routes_contents, 'Fibonacci::routes()')) {
            $filesystem->append(
                base_path('routes/web.php'),
                "\n\nRoute::group(['prefix' => 'admin'], function () {\n    Fibonacci::routes();\n});\n"
            );
        }
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
    }
    
    public function provides() {
        return ['fibonacci'];
    }
}