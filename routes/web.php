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

// --- Pasien ---
// Check apakah username / no handphone exist
$router->post('/pasien/validasi','ExampleController@checkPasien');
// Edit informasi pasien
$router->put('/pasien','ExampleController@editPasien');
// Ubah Password
$router->put('/pasien/password','ExampleController@editPasswordPasien');
// Get Info Pasien
$router->get('/pasien/{id}','ExampleController@getPasien');

// --- Administrator ---
// Admin Login
$router->post('/administrator/login','ExampleController@administratorLogin');

// --- Antrean ---
// Get Info Estimasi.
$router->get('/antrean/estimasi','ExampleController@getEstimasi');
// Get Info Antrean Hari Ini.
$router->get('/antrean/info','ExampleController@getAntreanInfo');
// Get Antrean aktif di user tertentu.
$router->get('/antrean/pasien/{id}','ExampleController@getAntreanWithPasienId');
// Get Riwayat Antrean berdasarkan User.
$router->get('/antrean/pasien/riwayat/{id}','ExampleController@getRiwayatWithPasienId');
// Get Riwayat Antrean berdasarkan Poliklinik.
$router->get('/antrean/poliklinik/riwayat/{id}','ExampleController@getRiwayatWithPoliId');
// Get Antrean berdasarkan Poliklinik (Antrean Utama).
$router->get('/antrean/poliklinik/utama/{id}','ExampleController@getAntreanWithPoliId');
// Get Antrean berdasarkan Poliklinik (Antrean Sementara).
$router->get('/antrean/poliklinik/sementara/{id}','ExampleController@getAntreanWithPoliIdSementara');
// Get Riwayat berdasarkan Poliklinik (Antrean Selesai).
$router->get('/antrean/poliklinik/selesai/{id}','ExampleController@getAntreanSelesaiWithPoliId');
// Update Antrean Status.
$router->put('/antrean','ExampleController@editAntrean');
// Insert Antrean.
$router->post('/antrean','ExampleController@insertAntrean');
// Insert Antrean Admin.
$router->post('/antrean/admin/normal','ExampleController@insertAntreanNormal');
// Insert Antrean Admin Gawat.
$router->post('/antrean/admin/gawat','ExampleController@insertAntreanGawat');

// --- Poliklinik ---
// Buka Portal.
$router->get('/poliklinik/buka','ExampleController@bukaPortal');
// Tutup Portal.
$router->get('/poliklinik/tutup','ExampleController@tutupPortal');
// Get All Poliklinik
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