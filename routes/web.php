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

/* --- API tanpa auth --- */
$router->group(['prefix' => 'api'], function () use ($router) {
    /* --- Pasien --- */
    $router->group(['prefix' => 'pasien'], function () use ($router) {
        // Registrasi pasien
        $router->post('/register', 'PasienController@register');
        // Check apakah username / no handphone exist
        $router->post('/validasi', 'PasienController@checkPasien');
        // Login dengan username
        $router->post('/login/username', 'PasienController@loginDenganUsername');
        // Login dengan nomor handphone
        $router->post('/login/nohp', 'PasienController@loginDenganNoHp');
    });
    /* --- Administrator --- */
    $router->group(['prefix' => 'administrator'], function () use ($router) {
        // Admin login
        $router->post('/login', 'AdministratorController@administratorLogin');
    });
    /* --- Perawat --- */
    $router->group(['prefix' => 'perawat'], function () use ($router) {
        // Perawat Login.
        $router->post('/login','PerawatController@loginPerawat');
    });
});


/* --- API dengan auth --- */
$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {
    /* --- Pasien --- */
    $router->group(['prefix' => 'pasien'], function () use ($router) {
        // Logout
        $router->post('/logout', 'PasienController@logout');
        // Get Info Pasien
        $router->get('/{id}','PasienController@getPasien');
        // Edit informasi pasien
        $router->put('/edit','PasienController@editPasien');
        // Ubah Password
        $router->put('/edit/password','PasienController@editPasswordPasien');
    });
    /* --- Perawat --- */
    $router->group(['prefix' => 'perawat'], function () use ($router) {
        // Menambahkan perawat
        $router->post('/insert','PerawatController@insertPerawat');
        // Edit Perawat (Id tertentu).
        $router->put('/edit/{id}','PerawatController@editPerawat');
        // Delete Perawat (Id tertentu).
        $router->delete('/delete/{id}','PerawatController@deletePerawat');
        // Get Perawat (Semua).
        $router->get('/','PerawatController@getAllPerawat');
        // Get Perawat (Id tertentu).
        $router->get('/{id}','PerawatController@getPerawat');
    });
    /* --- Antrean --- */
    $router->group(['prefix' => 'antrean'], function () use ($router) {
        // Get Info Estimasi.
        $router->post('/estimasi', 'AntreanController@getEstimasi');
        // Get Info Antrean Hari Ini.
        $router->get('/info','AntreanController@getAntreanInfo');
        // Get Antrean aktif di user tertentu.
        $router->get('/pasien/{id}','AntreanController@getAntreanWithPasienId');
        // Get Riwayat Antrean berdasarkan User.
        $router->get('/pasien/riwayat/{id}','AntreanController@getRiwayatWithPasienId');
        // Get Riwayat Antrean berdasarkan Poliklinik.
        $router->get('/poliklinik/riwayat/{id}','AntreanController@getRiwayatWithPoliId');
        // Get Antrean berdasarkan Poliklinik (Antrean Utama).
        $router->get('/poliklinik/utama/{id}','AntreanController@getAntreanWithPoliId');
        // Get Antrean berdasarkan Poliklinik (Antrean Sementara).
        $router->get('/poliklinik/sementara/{id}','AntreanController@getAntreanWithPoliIdSementara');
        // Get Riwayat berdasarkan Poliklinik (Antrean Selesai).
        $router->get('/poliklinik/selesai/{id}','AntreanController@getAntreanSelesaiWithPoliId');
        // Update Antrean Status.
        $router->put('/edit','AntreanController@editAntrean');
        // Insert Antrean.
        $router->post('/insert','AntreanController@insertAntrean');
        // Insert Antrean by Admin.
        $router->post('/insert/admin','AntreanController@insertAntreanNormal');
        // Insert Antrean Gawat.
        $router->post('/insert/admin/gawat','AntreanController@insertAntreanGawat');
    });
    /* --- Poliklinik --- */
    $router->group(['prefix' => 'poliklinik'], function () use ($router) {
        // Get Poliklinik (Semua).
        $router->get('/','PoliklinikController@getAllPoliklinik');
        // Get Poliklinik (Id tertentu).
        $router->get('/{id}','PoliklinikController@getPoliklinik');
        // Insert Poliklinik.
        $router->post('/insert','PoliklinikController@insertPoliklinik');
        // Edit Status Poliklinik (portal).
        $router->put('/status','PoliklinikController@ubahStatusAllPoli');
        // Edit Poliklinik
        $router->put('/edit/{id}','PoliklinikController@ubahPoliklinik');
        // Delete Poliklinik
        $router->delete('/delete/{id}','PoliklinikController@deletePoliklinik');
        // Method ada, tetapi tidak digunakan di production. (Mempertimbangkan apabila ada data yang berelasi)
    });
});

