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

$router->get('/administrator/login','ExampleController@administratorLogin');

$router->get('/admin','ExampleController@getAdmin');

// Daftar Antre Hari Ini
$router->post('/ticket/daftar','ExampleController@registerAntreanHariIni');

// Cek apakah sudah ambil tiket atau belum.
$router->get('/ticket/check','ExampleController@checkStatusTicket');

// Ubah status
$router->put('/ticket/ubah','ExampleController@ubahAntrean');

// --- Antrean ---
// Get Antrean berdasarkan Poliklinik.
$router->get('/antrean/poliklinik/{id}','ExampleController@getAntreanWithPoliId');

// --- Poliklinik ---
// Get Poliklinik (Semua).
$router->get('/poliklinik','ExampleController@getAllPoliklinik');
// Get Poliklinik (Id tertentu).
$router->get('/poliklinik/{id}','ExampleController@getPoliklinik');
// Insert Poliklinik.
$router->post('/poliklinik','ExampleController@insertPoliklinik');
// Edit Status Poliklinik (portal).
$router->put('/poliklinik/status','ExampleController@ubahStatusAllPoli');
// Edit Poliklinik
$router->put('/poliklinik/{id}','ExampleController@ubahPoliklinik');
// Delete Poliklinik
$router->delete('/poliklinik/{id}','ExampleController@deletePoliklinik');
// Method ada, tetapi tidak digunakan di production. (Mempertimbangkan apabila ada data yang berelasi)

// --- Perawat ---
// Insert Perawat.
$router->post('/perawat','ExampleController@insertPerawat');
// Edit Perawat (Id tertentu).
$router->put('/perawat/{id}','ExampleController@editPerawat');
// Delete Perawat (Id tertentu).
$router->delete('/perawat/{id}','ExampleController@deletePerawat');
// Get Perawat (Semua).
$router->get('/perawat','ExampleController@getAllPerawat');
// Get Perawat (Id tertentu).
$router->get('/perawat/{id}','ExampleController@getPerawat');
// Perawat Login.
$router->post('/perawat/login','ExampleController@loginPerawat');