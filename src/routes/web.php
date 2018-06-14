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
            Route::get('{table}/api/edit', ['uses' => $namespacePrefix.'ManageAPIController@addEditAPI', 'as' => 'edit']);
            Route::put('api/{id}', ['uses' => $namespacePrefix.'ManageAPIController@updateAPI',  'as' => 'update']);
            Route::get('{table}/api/create', ['uses' => $namespacePrefix.'ManageAPIController@addAPI',     'as' => 'create']);
            Route::post('api', ['uses' => $namespacePrefix.'ManageAPIController@storeAPI',   'as' => 'store']);
            Route::delete('api/{id}', ['uses' => $namespacePrefix.'ManageAPIController@deleteAPI',  'as' => 'delete']);
        });

        Route::group([
        ], function () use ($namespacePrefix) {
            Route::get('reports', ['uses' => $namespacePrefix.'ReportsController@all', 'as' => 'all']);
            Route::get('manage', ['uses' => $namespacePrefix.'ReportsController@manage', 'as' => 'manage']);
            Route::post('reports/update', ['uses' => $namespacePrefix.'ReportsController@update',  'as' => 'update']);
            Route::post('reports/store', ['uses' => $namespacePrefix.'ReportsController@store',   'as' => 'store']);
            Route::delete('reports/delete/{id}', ['uses' => $namespacePrefix.'ReportsController@delete',  'as' => 'delete']);
            Route::post('reports/filter', ['uses' => $namespacePrefix.'ReportsController@ajax',  'as' => 'filterReport']);


        });

        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});

// Database Routes

/*
Route::group(['as' => 'voyager.'], function () {
    event(new Routing());
    $namespacePrefix='\\Kadevjo\\Fibonacci\\Controllers\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());
        Route::get('bread', "{$namespacePrefix}BreadController@index");
        Route::get('index', "{$namespacePrefix}BreadController@index");
        Route::get('{table}/create', "{$namespacePrefix}BreadController@create");
        Route::delete('{id}', "{$namespacePrefix}BreadController@destroy");
        Route::get('binnacle', "{$namespacePrefix}BinnacleController@binnacle");
        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});
*/


