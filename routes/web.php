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
    $router->group(['prefix' => 'pasien'], function () use ($router) {
        $router->post('/register', 'AuthPasienController@register');
        $router->post('/login/username', 'AuthPasienController@loginDenganUsername');
        $router->post('/login/nohp', 'AuthPasienController@loginDenganNoHp');
        $router->post('/logout', 'PasienController@logout');
    });
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

// Ubah Poliklinik
$router->put('/poliklinik/ubah','ExampleController@ubahPoliklinik');

// Ubah status semua poli.
$router->put('/poliklinik/status','ExampleController@ubahStatusAllPoli');
