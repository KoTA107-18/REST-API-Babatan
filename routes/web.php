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

$router->get('/testElo', 'ExampleController@testElo');

/* --- API --- */
$router->group(['prefix' => 'api'], function () use ($router) {
    /* --- Pasien --- */
    $router->group(['prefix' => 'pasien'], function () use ($router) {
        // Registrasi pasien
        $router->post('/register', 'AuthPasienController@register');
        // Check apakah username / no handphone exist
        $router->post('/validasi', 'ExampleController@checkPasien');
        // Login dengan username
        $router->post('/login/username', 'AuthPasienController@loginDenganUsername');
        // Login dengan nomor handphone
        $router->post('/login/nohp', 'AuthPasienController@loginDenganNoHp');
        // Logout
        $router->post('/logout', 'PasienController@logout');
    });
    /* --- Administrator --- */
    $router->group(['prefix' => 'administrator'], function () use ($router) {
        // Admin login
        $router->post('/login', 'ExampleController@administratorLogin');
    });
    /* --- Perawat --- */
    $router->group(['prefix' => 'perawat'], function () use ($router) {
        // Menambahkan perawat
        $router->post('/insert','ExampleController@insertPerawat');
        // Edit Perawat (Id tertentu).
        $router->put('/edit/{id}','ExampleController@editPerawat');
        // Delete Perawat (Id tertentu).
        $router->delete('/delete/{id}','ExampleController@deletePerawat');
        // Get Perawat (Semua).
        $router->get('/','ExampleController@getAllPerawat');
        // Get Perawat (Id tertentu).
        $router->get('/{id}','ExampleController@getPerawat');
        // Perawat Login.
        $router->post('/login','ExampleController@loginPerawat');
    });
    /* --- Antrean --- */
    $router->group(['prefix' => 'antrean'], function () use ($router) {
        // Get Info Estimasi.
        $router->post('/estimasi', 'ExampleController@getEstimasi');
        // Get Info Antrean Hari Ini.
        $router->get('/info','ExampleController@getAntreanInfo');
        // Get Antrean aktif di user tertentu.
        $router->get('/pasien/{id}','ExampleController@getAntreanWithPasienId');
        // Get Riwayat Antrean berdasarkan User.
        $router->get('/pasien/riwayat/{id}','ExampleController@getRiwayatWithPasienId');
        // Get Riwayat Antrean berdasarkan Poliklinik.
        $router->get('/poliklinik/riwayat/{id}','ExampleController@getRiwayatWithPoliId');
        // Get Antrean berdasarkan Poliklinik (Antrean Utama).
        $router->get('/poliklinik/utama/{id}','ExampleController@getAntreanWithPoliId');
        // Get Antrean berdasarkan Poliklinik (Antrean Sementara).
        $router->get('/poliklinik/sementara/{id}','ExampleController@getAntreanWithPoliIdSementara');
        // Get Riwayat berdasarkan Poliklinik (Antrean Selesai).
        $router->get('/poliklinik/selesai/{id}','ExampleController@getAntreanSelesaiWithPoliId');
        // Update Antrean Status.
        $router->put('/edit','ExampleController@editAntrean');
        // Insert Antrean.
        $router->post('/insert','ExampleController@insertAntrean');
    });
    /* --- Poliklinik --- */
    $router->group(['prefix' => 'poliklinik'], function () use ($router) {
        // Get Poliklinik (Semua).
        $router->get('/','ExampleController@getAllPoliklinik');
        // Get Poliklinik (Id tertentu).
        $router->get('/{id}','ExampleController@getPoliklinik');
        // Insert Poliklinik.
        $router->post('/insert','ExampleController@insertPoliklinik');
        // Edit Status Poliklinik (portal).
        $router->put('/status','ExampleController@ubahStatusAllPoli');
        // Edit Poliklinik
        $router->put('/edit/{id}','ExampleController@ubahPoliklinik');
        // Delete Poliklinik
        $router->delete('/delete/{id}','ExampleController@deletePoliklinik');
        // Method ada, tetapi tidak digunakan di production. (Mempertimbangkan apabila ada data yang berelasi)
    });
});

