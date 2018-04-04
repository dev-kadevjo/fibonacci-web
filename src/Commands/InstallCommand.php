<?php

namespace Kadevjo\Fibonacci\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $name = 'fibonacci:install';
    
    public function handle(Filesystem $filesystem)
    {
        $this->info('Adding fibonacci routes to routes/web.php');
        $routes_contents = $filesystem->get(base_path('routes/web.php'));
        if (false === strpos($routes_contents, 'Fibonacci::routes()')) {
            $filesystem->append(
                base_path('routes/web.php'),
                "\n\nRoute::group(['prefix' => 'admin'], function () {\n    Fibonacci::routes();\n});\n"
            );
        }
    
    }
}