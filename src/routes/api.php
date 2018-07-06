<?php

use Illuminate\Http\Request;
use TCG\Voyager\Events\Routing;
use TCG\Voyager\Events\RoutingAdmin;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Database\Schema\SchemaManager;

$namespacePrefix='\\Kadevjo\\Fibonacci\\Controllers\\';
Route::group(['as' => 'fibonacci.'], function ()use ($namespacePrefix)
{
    try
    {
        foreach (SchemaManager::listTableNames() as $value)
        {
            $breadController =  $namespacePrefix.'APIController';
            //Route::resource($value, $breadController);
            Route::get($value, $breadController.'@index');
            Route::get($value.'/{id}', $breadController.'@show');
            Route::post($value, $breadController.'@store');
            Route::put($value.'/{id}', $breadController.'@update');
            Route::delete($value.'/{id}', $breadController.'@destroy');
        }
    }
    catch (\InvalidArgumentException $e)
    {
        throw new \InvalidArgumentException('Custom routes hasnt been configured because: '.$e->getMessage(), 1);
    }
    catch (\Exception $e)
    {
        // do nothing, might just be because table not yet migrated.
    }

    Route::post('/login-provider',$namespacePrefix.'Auth\SocialAuthController@login');

    //jwt authentication routes
    Route::post('login',$namespacePrefix.'Auth\JwtAuthController@login');
    Route::post('signup', $namespacePrefix.'Auth\JwtAuthController@signup');
    
    Route::middleware('auth:api')->group(function ()use ($namespacePrefix) {
        Route::post('logout',$namespacePrefix.'Auth\JwtAuthController@logout');
        Route::post('refresh',$namespacePrefix.'Auth\JwtAuthController@refresh');
        Route::get('me', $namespacePrefix.'Auth\JwtAuthController@me');
        
    });
});


