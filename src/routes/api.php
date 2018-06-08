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
            Route::resource($value, $breadController);
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

    Route::post('/login-provider',$namespacePrefix.'AuthAPIController@socialAuth');

    //jwt authentication routes


    Route::post('login',$namespacePrefix.'Auth\JwtApiController@login');

    Route::middleware('jwt.auth')->group(function ()use ($namespacePrefix) {
        Route::post('logout',$namespacePrefix.'Auth\JwtApiController@logout');
        Route::post('refresh',$namespacePrefix.'Auth\JwtApiController@refresh');
        Route::get('me', $namespacePrefix.'Auth\JwtApiController@me');
    });

});

Route::post('/getPublicToken',$namespacePrefix.'APIController@getToken');

