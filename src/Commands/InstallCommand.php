<?php

namespace Kadevjo\Fibonacci\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Kadevjo\Fibonacci\Installation\CreateMenu;
use Kadevjo\Fibonacci\FibonacciServiceProvider;

class InstallCommand extends Command
{
  protected $name = 'fibonacci:install';
  
  public function handle(Filesystem $filesystem)
  {    
    $this->info('Migrating the database tables into your application');
    $this->call('migrate', array('--path' => 'vendor/kadevjo/fibonacci/src/Database/Migrations'));
    
    $this->info('Publishing config files');
    $this->call('vendor:publish', ['--provider' => FibonacciServiceProvider::class]);
    
    $this->info('Creating menu items');
    $newMenu = CreateMenu::buildMenu();
    
    $this->info('Adding fibonacci routes to routes/web.php');
    $routes_contents = $filesystem->get(base_path('routes/web.php'));
    
    if (false === strpos($routes_contents, 'Fibonacci::webRoutes()')) {
      $filesystem->append(
        base_path('routes/web.php'),
        "\n\nRoute::group(['prefix' => 'admin'], function () {\n    Fibonacci::webRoutes();\n});\n"
      );
    }
    
    $this->info('Adding Fibonacci API routes to routes/api.php');        
    
    $routes_contents = $filesystem->get(base_path('routes/api.php'));
    
    if (false === strpos($routes_contents, 'Fibonacci::apiRoutes()')) {
      $filesystem->append(
        base_path('routes/api.php'),
        "\n\n    Fibonacci::apiRoutes();\n"
      );
    }

  }
}