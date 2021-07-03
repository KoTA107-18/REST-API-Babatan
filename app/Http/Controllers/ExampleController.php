<?php
namespace App\Http\Controllers;
use App\Models\Jadwal;
use App\Models\JadwalPasien;
use App\Models\Perawat;
use App\Models\Poliklinik;
use App\Models\RiwayatAntrean;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // try{
        //     // try code
        // }
        // catch(\Exception $e){
        //     // catch code
        // }
    }

    // Administrator
    public function administratorLogin(Request $request){
        $username = $request["username"];
        $password = $request["password"];
        $result = DB::select("SELECT * FROM administrator WHERE username_admin = '$username' AND password_admin = '$password'");
        if($result != null){
            return response()->json(true, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function checkPasien(Request $request){
        $username = $request["username"];
        $no_handphone = $request["no_handphone"];
        $resultUsername     = DB::select("SELECT * FROM pasien WHERE username = '$username'");
        $resultHandphone    = DB::select("SELECT * FROM pasien WHERE no_handphone = '$no_handphone'");
        if(($resultUsername != null) && ($resultHandphone != null)){
            return response()->json([
                'success'   => false,
                'message'   => 'Username dan Nomor Handphone anda telah digunakan!',
                'data'      => ''
            ], 409);
        } else if($resultUsername != null && $resultHandphone == null){
            return response()->json([
                'success'   => false,
                'message'   => 'Username anda telah digunakan!',
                'data'      => ''
            ], 409);
        } else if($resultUsername == null && $resultHandphone != null){
            return response()->json([
                'success'   => false,
                'message'   => 'Nomor Handphone anda telah digunakan!',
                'data'      => ''
            ], 409);
        } else {
            return response()->json([
                'success'   => true,
                'message'   => 'Berhasil!',
                'data'      => ''
            ], 200);
        }

    }

    // Antrean
    public function getEstimasi(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));

        $id_poli        = $request->input('id_poli');
        $tgl_pelayanan  = $request->input('tgl_pelayanan');
        $jam_booking    = $request->input('jam_booking');

        $antreanDiatas  = JadwalPasien::find($id_poli)->where([
            ['tgl_pelayanan'    , '='   , $tgl_pelayanan],
            ['jam_booking'      , '<'   , $jam_booking]
        ])->where( function($q) {
            $q->where('status_antrean'  , '='  , 1)
              ->orWhere('status_antrean', '='  , 4);
        })->get();

        $resultInfoPoliklinik = Poliklinik::find($id_poli);
        $rataRata = $resultInfoPoliklinik->rerata_waktu_pelayanan;

        $estimasiAntrean = 0;
        $jam_booking_top = null;
        if(count($antreanDiatas) != 0){
            $estimasiAntrean = count($antreanDiatas) * $rataRata;
            $jam_booking_top = $antreanDiatas[0]->jam_booking;
        }

        if($jam_booking_top == null){
            return response()->json($jam_booking, 200);
        }

        if(date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jam_booking_top))){
            $jamEstimasiAkhir = date("H:i", strtotime($CURRENT_TIME . ' + ' . $estimasiAntrean . ' minutes'));
            if(date("H:i", strtotime($jamEstimasiAkhir)) > date("H:i", strtotime($jam_booking))){
                return response()->json($jamEstimasiAkhir, 200);
            } else {
                return response()->json($jam_booking, 200);
            }
        } else {
            return response()->json($jam_booking, 200);
        }
    }

    public function getAntreanInfo(){
        date_default_timezone_set("Asia/Jakarta");

        $resultPoli = Poliklinik::with('totalAntrean', 'antreanSementara', 'nomorAntrean')
                                ->selectRaw('id_poli, nama_poli, status_poli')
                                ->orderBy('id_poli', 'asc')
                                ->get();

        if($resultPoli != null){
            return response()->json($resultPoli, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanWithPoliId(Request $request, $id){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
            ->where( function($q) {
               $q->where('status_antrean', '=', 1)
                 ->orWhere('status_antrean', '=', 2);})
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
            ->orderBy('jam_booking', 'asc')->get();

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getRiwayatWithPoliId(Request $request, $id){
        $result = RiwayatAntrean::where('id_poli', '=', $id)->orderBy('jam_booking', 'asc')->get();
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getRiwayatWithPasienId(Request $request, $id){
        $result = RiwayatAntrean::where('id_pasien', '=', $id)->get();
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanWithPasienId(Request $request, $id){
        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
            ->where('status_antrean', '!=', 3)
            ->where('status_antrean', '!=', 5)
            ->where('id_pasien', '=', $id)
            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
            ->get();

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanWithPoliIdSementara(Request $request, $id){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
            ->where('status_antrean', '=', 4)
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
            ->orderBy('jam_booking', 'asc')->get();

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanSelesaiWithPoliId(Request $request, $id){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
            ->where( function($q) {
                $q->where('status_antrean', '=', 3)
                    ->orWhere('status_antrean', '=', 5);})
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
            ->orderBy('jam_booking', 'asc')->get();

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function editAntrean(Request $request){
        /*
        BELUM_DILAYANI = 1;
        SEDANG_DILAYANI = 2;
        SUDAH_DILAYANI = 3;
        DILEWATI = 4;
        DIBATALKAN = 5; */
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $id_poli = $request["id_poli"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $id_pasien = $request["id_pasien"];
        $status_antrean = $request["status_antrean"];

        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
            ->where('tgl_pelayanan', '=', $tgl_pelayanan)
            ->where('id_poli', '=', $id_poli)
            ->where('id_pasien', '=', $id_pasien)
            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
            ->orderBy('jam_booking', 'asc')->get();

        // Jika status selesai / cancel. Langsung dipindah ke entitas Riwayat
        if(($status_antrean == 5) || ($status_antrean == 3)){
            // DB::delete("DELETE FROM jadwal_pasien WHERE id_poli='$id_poli' AND hari='$hari' AND id_pasien='$id_pasien'");
            $data = [
                'id_poli'               => $id_poli,
                'id_pasien'             => $id_pasien,
                'nomor_antrean'         => $result[0]->nomor_antrean,
                'tipe_booking'          => $result[0]->tipe_booking,
                'tgl_pelayanan'         => $result[0]->tgl_pelayanan,
                'jam_booking'           => $result[0]->jam_booking,
                'waktu_daftar_antrean'  => $result[0]->waktu_daftar_antrean,
                'jam_mulai_dilayani'    => $result[0]->jam_mulai_dilayani,
                'jam_selesai_dilayani'  => $result[0]->jam_selesai_dilayani,
                'status_antrean'        => $status_antrean,
                'nama_poli'             => $result[0]->poliklinik->nama_poli,
                'username'              => $result[0]->pasien->username,
                'no_handphone'          => $result[0]->pasien->no_handphone,
                'kepala_keluarga'       => $result[0]->pasien->kepala_keluarga,
                'tgl_lahir'             => $result[0]->pasien->tgl_lahir,
                'alamat'                => $result[0]->pasien->alamat,
                'nama_lengkap'          => $result[0]->pasien->nama_lengkap,
                'jenis_pasien'          => $result[0]->pasien->jenis_pasien,
            ];

            RiwayatAntrean::create($data);
        }

        $CURRENT_TIME = date("H:i", strtotime("now"));
        if($status_antrean == 2){
            JadwalPasien::where('tgl_pelayanan', '=', $tgl_pelayanan)
                        ->where('id_poli', '=', $id_poli)
                        ->where('id_pasien', '=', $id_pasien)
                        ->update(['status_antrean' => $status_antrean, 'jam_mulai_dilayani' => $CURRENT_TIME]);
        } else if($status_antrean == 3){
            JadwalPasien::where('tgl_pelayanan', '=', $tgl_pelayanan)
                        ->where('id_poli', '=', $id_poli)
                        ->where('id_pasien', '=', $id_pasien)
                        ->update(['status_antrean' => $status_antrean, 'jam_selesai_dilayani' => $CURRENT_TIME]);
        } else {
            JadwalPasien::where('tgl_pelayanan', '=', $tgl_pelayanan)
                        ->where('id_poli', '=', $id_poli)
                        ->where('id_pasien', '=', $id_pasien)
                        ->update(['status_antrean' => $status_antrean]);
        }

    }

    // Kebutuhan Insert Bukan Booking

    private function isAmbilAntrean(int $id_pasien): bool
    {
        $resultCheckRegist = JadwalPasien::where('id_pasien', '=', $id_pasien)
                                         ->where('status_antrean', '!=', 3)
                                         ->where('status_antrean', '!=', 5)
                                         ->get();

        return ($resultCheckRegist != null);
    }

    private function isPoliklinikAktif(int $id_poli): bool
    {
        $resultCheckRegist = Poliklinik::where('id_poli', '=', $id_poli)
                                       ->where('status_poli', '=', 1)
                                       ->get();

        return ($resultCheckRegist != null);
    }

    private function kuotaNonBooking(
        string $hari,
        int $id_poli,
        int $id_pasien,
        int $jenis_pasien): bool
    {
            date_default_timezone_set("Asia/Jakarta");
            $status = false;
            $CURRENT_DATE = date("Y-m-d", strtotime("now"));
            $CURRENT_TIME = date("H:i", strtotime("now"));
            $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));
            $jamIterator = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));
            $resultInfoPoliklinik = Poliklinik::with(['jadwal' => function($q) use ($id_poli, $hari) {
                                                    $q->where('hari', '=', $hari);
                                                }])->where('id_poli', '=', $id_poli)->get();
            $resultAntrean = JadwalPasien::where('id_poli', '=', $id_poli)->where('tgl_pelayanan', '=', $CURRENT_DATE)->get();
            $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
            $jamTutup = $resultInfoPoliklinik[0]->jadwal->jam_tutup_booking;
            $kuota = floor(60/$rataRata);


            while (date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator))) {
                $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
            }

            while (($status == false) AND ($jamIterator < $jamTutup)){
                $result = JadwalPasien::where('id_poli', '=', $id_poli)
                                      ->where('tgl_pelayanan', '=', $CURRENT_DATE)
                                      ->where('jam_booking', '=', $jamIterator)
                                      ->where('status_antrean', '!=', 5)
                                      ->get();

                if(!$result->isEmpty()){
                    $status = true;
                }
                if($status != true){
                    $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
                }
            }

            if($status){
                $data = [
                    'id_poli'               => $id_poli,
                    'hari'                  => $hari,
                    'id_pasien'             => $id_pasien,
                    'nomor_antrean'         => 0,
                    'tipe_booking'          => 0,
                    'tgl_pelayanan'         => $CURRENT_DATE,
                    'jam_booking'           => $jamIterator,
                    'waktu_daftar_antrean'  => $CURRENT_TIMEDATE,
                    'jam_mulai_dilayani'    => NULL,
                    'jam_selesai_dilayani'  => NULL,
                    'status_antrean'        => 1,
                ];
                JadwalPasien::create($data);
                return true;
            } else {
                return false;
            }
    }

    // Kebutuhan Insert Booking
    private function isJadwalTersedia(string $id_poli, string $hari, string $jam_booking){
        $resultCheckRegist = Poliklinik::with([
                                'jadwal' => function($q) use ($hari, $jam_booking){
                                    $q->where('hari', '=', $hari)
                                      ->where('jam_buka_booking', '<=', $jam_booking)
                                      ->where('jam_tutup_booking', '>=', $jam_booking);
                                }
                            ])->where('id_poli', '=', $id_poli)->get();

        return (!$resultCheckRegist->isEmpty());
    }

    public function testElo(Request $request){
//        return response()->json(
//            Poliklinik::with('jadwalPasien')->where( 'id_poli',1)->where(function($query){
//                $query->where('id_poli', '<=', 4)
//                    ->orWhere('id_poli', '>', 4);
//            })->count('id_poli'),
//            200
//        );
//        return response()->json(
//            Poliklinik::with('totalAntrean', 'antreanSementara', 'nomorAntrean')->selectRaw('id_poli, nama_poli, status_poli')->get(),
//            200
//        );
//        $result = JadwalPasien::with('pasien:id_pasien,tgl_lahir', 'jadwal')->where( 'id_poli',1)->get();
        $id_poli = 1;
        $hari = 'RB';
        $tgl_pelayanan = '2021-06-23';
        $jam_booking = '08:00:00';
        $jamBookingIterator = $jam_booking;
        $jam = substr($jamBookingIterator, 0, 2) . '%';
        $result = JadwalPasien::where('id_poli', '=', $id_poli)->where('tgl_pelayanan', '=', $tgl_pelayanan)->where('jam_booking', 'LIKE', $jam)->get();
        return response()->json(
            $result,
            200
        );
        $id_pasien = 9;
        $status_antrean = 2;

//        $result = JadwalPasien::with('pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien', 'poliklinik:id_poli,nama_poli')
//            ->where('tgl_pelayanan', '=', $tgl_pelayanan)
//            ->where('id_poli', '=', $id_poli)
//            ->where('id_pasien', '=', $id_pasien)
//            ->selectRaw('id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking, waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari')
//            ->orderBy('jam_booking', 'asc')->get();

//        JadwalPasien::where('tgl_pelayanan', '=', $tgl_pelayanan)
//            ->where('id_poli', '=', $id_poli)
//            ->where('id_pasien', '=', $id_pasien)
//            ->update(['status_antrean' => $status_antrean, 'jam_mulai_dilayani' => '15:21:00']);
        // Jika status selesai / cancel. Langsung dipindah ke entitas Riwayat
//        if(true){
//            // DB::delete("DELETE FROM jadwal_pasien WHERE id_poli='$id_poli' AND hari='$hari' AND id_pasien='$id_pasien'");
//            $data = [
//                'id_poli'               => $id_poli,
//                'id_pasien'             => $id_pasien,
//                'nomor_antrean'         => $result[0]->nomor_antrean,
//                'tipe_booking'          => $result[0]->tipe_booking,
//                'tgl_pelayanan'         => $result[0]->tgl_pelayanan,
//                'jam_booking'           => $result[0]->jam_booking,
//                'waktu_daftar_antrean'  => $result[0]->waktu_daftar_antrean,
//                'jam_mulai_dilayani'    => $result[0]->jam_mulai_dilayani,
//                'jam_selesai_dilayani'  => $result[0]->jam_selesai_dilayani,
//                'status_antrean'        => $status_antrean,
//                'nama_poli'             => $result[0]->poliklinik->nama_poli,
//                'username'              => $result[0]->pasien->username,
//                'no_handphone'          => $result[0]->pasien->no_handphone,
//                'kepala_keluarga'       => $result[0]->pasien->kepala_keluarga,
//                'tgl_lahir'             => $result[0]->pasien->tgl_lahir,
//                'alamat'                => $result[0]->pasien->alamat,
//                'nama_lengkap'          => $result[0]->pasien->nama_lengkap,
//                'jenis_pasien'          => $result[0]->pasien->jenis_pasien,
//            ];
//
//            RiwayatAntrean::create($data);
//        }
    }

    private function kuotaBooking(
        string $hari,
        int $id_poli,
        int $id_pasien,
        int $jenis_pasien,
        string $tgl_pelayanan,
        string $jam_booking){
            date_default_timezone_set("Asia/Jakarta");
            // Inisialisasi
            $CURRENT_TIMEDATE = date("Y-m-d H:i:s", strtotime("now"));
            $jamBookingIterator = $jam_booking;
            $jam = substr($jamBookingIterator, 0, 2) . '%';
            $resultInfoPoliklinik = Poliklinik::where('id_poli', '=', $id_poli)->get();
            $resultAntrean = JadwalPasien::where('id_poli', '=', $id_poli)->where('tgl_pelayanan', '=', $tgl_pelayanan)->where('jam_booking', 'LIKE', $jam)->get();
            $antrean = 0;
            if(!$resultAntrean->isEmpty()){
                $antrean = count($resultAntrean);
            }
            $status = false;
            $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
            $kuota = floor(60/$rataRata);

            while (($status == false) && (substr($jamBookingIterator, 0, 2) == substr($jam_booking, 0, 2)) ) {
                $result = JadwalPasien::where([
                    ['id_poli', '=', $id_poli],
                    ['tgl_pelayanan', '=', $tgl_pelayanan],
                    ['jam_booking', '=', $jamBookingIterator],
                    ['status_antrean', '!=', 5]
                ])->get();

                if($result->isEmpty()){
                    $status = true;
                }
                if($status != true){
                    $jamBookingIterator = date("H:i", strtotime($jamBookingIterator . ' + ' . $rataRata . ' minutes'));
                }
            }

            if($status){
                $data = [
                    'id_poli'               => $id_poli,
                    'hari'                  => $hari,
                    'id_pasien'             => $id_pasien,
                    'nomor_antrean'         => 0,
                    'tipe_booking'          => 1,
                    'tgl_pelayanan'         => $tgl_pelayanan,
                    'jam_booking'           => $jamBookingIterator,
                    'waktu_daftar_antrean'  => $CURRENT_TIMEDATE,
                    'jam_mulai_dilayani'    => NULL,
                    'jam_selesai_dilayani'  => NULL,
                    'status_antrean'        => 1,
                ];
                JadwalPasien::create($data);

                return true;
            } else {
                return false;
            }
    }


    public function insertAntrean(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $hari = $request["hari"];
        $id_poli = $request["id_poli"];
        $id_pasien = $request["id_pasien"];
        $tipe_booking = $request["tipe_booking"];
        $jenis_pasien = $request["jenis_pasien"];

        // Jika sudah mengambil Antrean
        if($this->isAmbilAntrean($id_pasien) == true){
            return response()->json([
                'success'   => false,
                'message'   => 'Anda masih memiliki antrean berlangsung!',
                'data'      => ''
            ], 409);
        }

        // Jika Bukan Booking
        if($tipe_booking == 0){
            // Jika Poliklinik aktif.
            if($this->isPoliklinikAktif($id_poli)){
                // Proses Antrean
                if($this->kuotaNonBooking($hari, $id_poli, $id_pasien, $jenis_pasien)){
                    $this->sortNumber($id_poli, $CURRENT_DATE);
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], 200);
                } else {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Kuota pada hari ini tidak tersedia!',
                        'data'      => ''
                    ], 409);
                }
            } else {
                // Jika Poliklinik tidak aktif.
                // Tampilan pesan gagal.
                return response()->json([
                    'success'   => false,
                    'message'   => 'Poliklinik tidak aktif!',
                    'data'      => ''
                ], 409);
            }
        } else {
            // Jika Booking
            // Jika Poliklinik memiliki jadwal di waktu yang dipilih.
            $tgl_pelayanan = $request["tgl_pelayanan"];
            $jam_booking = $request["jam_booking"];
            if($this->isJadwalTersedia($id_poli, $hari, $jam_booking)){
                // Proses Antrean
                if($this->kuotaBooking($hari, $id_poli, $id_pasien, $jenis_pasien, $tgl_pelayanan, $jam_booking)){
                    $this->sortNumber($id_poli, $tgl_pelayanan);
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], 200);
                } else {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Kuota untuk jam yang anda pilih tidak tersedia!',
                        'data'      => ''
                    ], 409);
                }
            } else {
                // Jika tidak memiliki waktu
                // Tampilan pesan gagal
                return response()->json([
                    'success'   => false,
                    'message'   => 'Jadwal yang anda pilih tidak sesuai!',
                    'data'      => ''
                ], 409);
            }
        }

    }

    // Sort Nomor Antrean

    private function sortNumber(int $id_poli, string $tgl_pelayanan){
        $result = JadwalPasien::where([
            ['id_poli', '=', $id_poli],
            ['tgl_pelayanan', '=' , $tgl_pelayanan],
        ])->orderBy('jam_booking', 'asc')->orderBy('waktu_daftar_antrean', 'asc')->get();

        $i = 0;
        while ($i < count($result)) {
            $nomor = $i+1;
            $waktu_daftar_antrean = $result[$i]->waktu_daftar_antrean;
            $idPasien = $result[$i]->id_pasien;

            JadwalPasien::where('id_poli', '=', $id_poli)
                ->where('id_pasien', '=', $idPasien)
                ->where('waktu_daftar_antrean', '=', $waktu_daftar_antrean)
                ->where('tgl_pelayanan', '=', $tgl_pelayanan)
                ->update(['nomor_antrean' => $nomor]);
            $i++;
        }

    }


    // Poliklinik
    public function getAllPoliklinik(){
        $result = [];
        $resultPoli = Poliklinik::all();

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i] = $row;
            $idPoli = $row->id_poli;
            $resultJadwal = Jadwal::where('id_poli', '=', $idPoli)->get();
            $result[$i]->jadwal = $resultJadwal;
            $i++;
        }

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getPoliklinik($id){
        $result = [];
        $resultPoli = Poliklinik::where('id_poli', '=', $id)->get();

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i] = $row;
            $idPoli = $row->id_poli;
            $resultJadwal = Jadwal::where('id_poli', '=', $idPoli)->get();
            $result[$i]->jadwal = $resultJadwal;
            $i++;
        }

        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function insertPoliklinik(Request $request){
        $nama_poli = $request["nama_poli"];
        $desc_poli = $request["desc_poli"];
        $status_poli = $request["status_poli"];
        $rerata = $request["rerata_waktu_pelayanan"];
        DB::insert("INSERT poliklinik VALUES(0,'$nama_poli', '$desc_poli', $status_poli, $rerata)");
        $jadwal = $request["jadwal"];
        foreach ($jadwal as $jadwalPerHari) {
            $hari = $jadwalPerHari["hari"];
            $jam_buka_booking = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking = $jadwalPerHari["jam_tutup_booking"];
            $id_poli = Poliklinik::select('id_poli')->where('nama_poli', '=', $nama_poli)->get();

            $data = [
                'hari'              => $hari,
                'jam_buka_booking'  => $jam_buka_booking,
                'jam_tutup_booking' => $jam_tutup_booking,
                'id_poli'           => $id_poli,
            ];

            Jadwal::create($data);
        }
    }

    public function ubahPoliklinik(Request $request, $id){
        $id_poli        = $id;
        $nama_poli      = $request["nama_poli"];
        $desc_poli      = $request["desc_poli"];
        $status_poli    = $request["status_poli"];
        $rerata         = $request["rerata_waktu_pelayanan"];

        Poliklinik::where('id_poli', '=', $id_poli)
            ->update([
                'nama_poli'                 => $nama_poli,
                'desc_poli'                 => $desc_poli,
                'status_poli'               => $status_poli,
                'rerata_waktu_pelayanan'    => $rerata,
            ]);

        Jadwal::where('id_poli', '=', $id_poli)->delete();

        $jadwal = $request["jadwal"];
        foreach ($jadwal as $jadwalPerHari) {
            $hari = $jadwalPerHari["hari"];
            $jam_buka_booking = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking = $jadwalPerHari["jam_tutup_booking"];

            $data = [
                'hari'              => $hari,
                'jam_buka_booking'  => $jam_buka_booking,
                'jam_tutup_booking' => $jam_tutup_booking,
                'id_poli'           => $id_poli,
            ];

            Jadwal::create($data);
        }
    }

    public function ubahStatusAllPoli(Request $request){
        $i=0;
        while($request[$i] != null){
            $id = $request[$i]["id_poli"];
            $status = $request[$i]["status_poli"];
            Poliklinik::where('id_poli', '=', $id)->update(['status_poli' => $status]);
            $i++;
        }

        if($i != 0){
            return response()->json($i, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function deletePoliklinik($id){
        Jadwal::where('id_poli', '=', $id)->delete();
        Poliklinik::where('id_poli', '=', $id)->delete();
    }



    // Perawat.
    public function getAllPerawat(){
        $result = Perawat::with('poliklinik:id_poli,nama_poli')->get();

        if(!$result->isEmpty()){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function insertPerawat(Request $request){
        $data = [
            'username'  => $request["username"],
            'password'  => $request["password"],
            'nama'      => $request["nama"],
            'id_poli'   => $request["id_poli"]
        ];

        Perawat::create($data);
    }

    public function editPerawat(Request $request, $id){
        Perawat::where('id_perawat', '=', $id)->update([
            'username'  => $request["username"],
            'password'  => $request["password"],
            'nama'      => $request["nama"],
            'id_poli'   => $request["id_poli"]
        ]);
    }

    public function deletePerawat($id){
        Perawat::where('id_perawat', '=', $id)->delete();
    }

    public function getPerawat($id){
        $result = Perawat::with('poliklinik:id_poli,nama_poli')->where('id_perawat', '=', $id)->get();
        if(!$result->isEmpty()){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function loginPerawat(Request $request){
        $result = Perawat::where([
            ['username', '=', $request["username"]],
            ['password', '=', $request["password"]],
        ])->get();
        if(!$result->isEmpty()){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

}
