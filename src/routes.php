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