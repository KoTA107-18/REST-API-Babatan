<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/register', 'PasienController@register');

    $router->post('/login/username', 'PasienController@loginDenganUsername');

    $router->post('/login/nohp', 'PasienController@loginDenganNoHp');
});

$router->get('/pasien/login','ExampleController@pasienLogin');

$router->get('/administrator/login','ExampleController@administratorLogin');

$router->get('/admin','ExampleController@getAdmin');

$router->get('/poliklinik','ExampleController@getPoliklinik');

// Daftar Antre Hari Ini
$router->post('/ticket/daftar','ExampleController@registerAntreanHariIni');

// Cek apakah sudah ambil tiket atau belum.
$router->get('/ticket/check','ExampleController@checkStatusTicket');

// Ubah status
$router->put('/ticket/ubah','ExampleController@ubahAntrean');