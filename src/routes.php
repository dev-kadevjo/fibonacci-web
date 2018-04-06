<?php
use TCG\Voyager\Events\Routing;
use TCG\Voyager\Events\RoutingAdmin;
use TCG\Voyager\Events\RoutingAfter;
use TCG\Voyager\Events\RoutingAdminAfter;
use TCG\Voyager\Models\DataType;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// API Routes
Route::group(['as' => 'fibonacci.'], function () {
    event(new Routing());
    $namespacePrefix='\\Kadevjo\\Fibonacci\\Controllers\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());
        Route::group([
            'as'     => 'database.api.',
            'prefix' => 'database',
        ], function () use ($namespacePrefix) {
            Route::get('{table}/api/edit', ['uses' => $namespacePrefix.'APIController@addEditAPI', 'as' => 'edit']);
            Route::put('api/{id}', ['uses' => $namespacePrefix.'APIController@updateAPI',  'as' => 'update']);
            Route::get('{table}/api/create', ['uses' => $namespacePrefix.'APIController@addAPI',     'as' => 'create']);
            Route::post('api', ['uses' => $namespacePrefix.'APIController@storeAPI',   'as' => 'store']);
            Route::delete('api/{id}', ['uses' => $namespacePrefix.'APIController@deleteAPI',  'as' => 'delete']);
        });
        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});

// Database Routes
Route::group(['as' => 'voyager.'], function () {
    event(new Routing());
    $namespacePrefix='\\Kadevjo\\Fibonacci\\Controllers\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());
        Route::resource('database', "{$namespacePrefix}DatabaseController");
        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});