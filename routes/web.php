<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'schedule'], function () use ($router) {
    $router->get('get/{messengerID}',  ['uses' => 'ScheduleController@getTKB']);
    $router->get('setup',  ['uses' => 'ScheduleController@setupAccount']);
    $router->get('lichthi/{messengerID}', ['uses' => 'ScheduleController@getLichThi']);
    $router->get('update', ['uses' => 'ScheduleController@index']);
});
