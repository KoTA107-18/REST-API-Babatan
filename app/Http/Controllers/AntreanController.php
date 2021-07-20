<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\JadwalPasien;
use App\Models\Pasien;
use App\Models\Poliklinik;
use App\Models\RiwayatAntrean;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AntreanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // Antrean
    public function getEstimasi ( Request $request )
    {
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));

        $id_poli        = $request->input('id_poli');
        $tgl_pelayanan  = $request->input('tgl_pelayanan');
        $jam_booking    = $request->input('jam_booking');

        $antreanDiatas  = JadwalPasien::where([
            ['id_poli',         '=', $id_poli],
            ['tgl_pelayanan',   '=', $tgl_pelayanan],
            ['jam_booking',     '<', $jam_booking]
        ])->where( function($q) {
            $q->where('status_antrean'  , '='  , 1)
              ->orWhere('status_antrean', '='  , 4);
        })->orderBy('jam_booking', 'asc')->first();

        $resultInfoPoliklinik   = Poliklinik::find($id_poli);
        $rataRata               = $resultInfoPoliklinik->rerata_waktu_pelayanan;

        $estimasiAntrean = 0;
        $jam_booking_top = null;
        if ( count((array)$antreanDiatas) != 0 ) {
            $estimasiAntrean = count((array)$antreanDiatas) * $rataRata;
            $jam_booking_top = $antreanDiatas->jam_booking;
        }

        if($jam_booking_top == null){
            if(date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jam_booking))){
                return response()->json($CURRENT_TIME, Response::HTTP_OK);
            } else {
                return response()->json($jam_booking, Response::HTTP_OK);
            }
        }

        if ( date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jam_booking_top)) ) {
            $jamEstimasiAkhir = date("H:i", strtotime($CURRENT_TIME . ' + ' . $estimasiAntrean . ' minutes'));

            if ( date("H:i", strtotime($jamEstimasiAkhir)) > date("H:i", strtotime($jam_booking)) ) {
                return response()->json($jamEstimasiAkhir, Response::HTTP_OK);
            } else {
                return response()->json($jam_booking, Response::HTTP_OK);
            }
        } else {
            return response()->json($jam_booking, Response::HTTP_OK);
        }
    }

    public function getAntreanInfo () {
        date_default_timezone_set("Asia/Jakarta");

        $resultPoli = Poliklinik::with('totalAntrean', 'antreanSementara', 'nomorAntrean')
            ->selectRaw('id_poli, nama_poli, status_poli')
            ->orderBy('id_poli', 'asc')
            ->get();

        if ( !$resultPoli->isEmpty() ) {
            return response()->json($resultPoli, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getAntreanWithPasienId ( Request $request, $id )
    {
        $result = JadwalPasien::with(
            'pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien',
                    'poliklinik:id_poli,nama_poli'
            )->where('status_antrean', '!=', 3)
            ->where('status_antrean', '!=', 5)
            ->where('id_pasien', '=', $id)
            ->selectRaw(
                'id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking,'.
                         ' waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari'
            )->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getAntreanWithPoliId ( Request $request, $id )
    {
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $result = JadwalPasien::with(
            'pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien',
                    'poliklinik:id_poli,nama_poli'
            )->where( function($q) {
                $q->where('status_antrean', '=', 1)
                    ->orWhere('status_antrean', '=', 2);})
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw(
                'id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking,'.
                         ' waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari'
            )->orderBy('jam_booking', 'asc')->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getRiwayatWithPasienId ( Request $request, $id )
    {
        $result = RiwayatAntrean::with('pasien', 'poliklinik')->where('id_pasien', '=', $id)->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getRiwayatWithPoliId ( Request $request, $id )
    {
        $result = RiwayatAntrean::with('pasien', 'poliklinik')->where('id_poli', '=', $id)->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getAntreanWithPoliIdSementara ( Request $request, $id )
    {
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $result = JadwalPasien::with(
            'pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien',
                    'poliklinik:id_poli,nama_poli'
            )->where('status_antrean', '=', 4)
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw(
                'id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking,'.
                         ' waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari'
            )->orderBy('jam_booking', 'asc')->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getAntreanSelesaiWithPoliId ( Request $request, $id )
    {
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $result = JadwalPasien::with(
            'pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien',
                    'poliklinik:id_poli,nama_poli'
            )->where( function($q) {
                $q->where('status_antrean', '=', 3)
                    ->orWhere('status_antrean', '=', 5);})
            ->where('tgl_pelayanan', '=', $CURRENT_DATE)
            ->where('id_poli', '=', $id)
            ->selectRaw(
                'id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking,'.
                         ' waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari'
            )->orderBy('jam_booking', 'asc')->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function editAntrean ( Request $request )
    {
        /*
         * BELUM_DILAYANI   1
         * SEDANG_DILAYANI  2
         * SUDAH_DILAYANI   3
         * DILEWATI         4
         * DIBATALKAN       5
         */

        date_default_timezone_set('Asia/Jakarta');
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $id_poli        = $request->input('id_poli');
        $tgl_pelayanan  = $request->input('tgl_pelayanan');
        $id_pasien      = $request->input('id_pasien');
        $status_antrean = $request->input('status_antrean');

        if ( $status_antrean == 2 ) {
            JadwalPasien::where('tgl_pelayanan', '=', $tgl_pelayanan)
                ->where('id_poli', '=', $id_poli)
                ->where('id_pasien', '=', $id_pasien)
                ->update(['status_antrean' => $status_antrean, 'jam_mulai_dilayani' => $CURRENT_TIME]);
        } else if ( $status_antrean == 3 ) {
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

        // Jika status selesai / cancel. Langsung dipindah ke entitas Riwayat
        if ( ( $status_antrean == 5 ) || ( $status_antrean == 3 ) ) {
            $result = JadwalPasien::with(
                'pasien:id_pasien,username,no_handphone,kepala_keluarga,nama_lengkap,alamat,tgl_lahir,jenis_pasien',
                        'poliklinik:id_poli,nama_poli'
                )->where('tgl_pelayanan', '=', $tgl_pelayanan)
                ->where('id_poli', '=', $id_poli)
                ->where('id_pasien', '=', $id_pasien)
                ->selectRaw(
                    'id_poli, id_pasien, nomor_antrean, tipe_booking, tgl_pelayanan, jam_booking,'.
                             ' waktu_daftar_antrean, jam_mulai_dilayani, jam_selesai_dilayani, status_antrean, hari'
                )->orderBy('jam_booking', 'asc')->first();

            $data = [
                'id_poli'               => $id_poli,
                'id_pasien'             => $id_pasien,
                'nomor_antrean'         => $this->nullIf( $result->nomor_antrean, '' ),
                'tipe_booking'          => $result->tipe_booking,
                'tgl_pelayanan'         => $result->tgl_pelayanan,
                'jam_booking'           => $this->nullIf( $result->jam_booking, '' ),
                'waktu_daftar_antrean'  => $result->waktu_daftar_antrean,
                'jam_mulai_dilayani'    => $this->nullIf( $result->jam_mulai_dilayani, '' ),
                'jam_selesai_dilayani'  => $this->nullIf( $result->jam_selesai_dilayani, '' ),
                'latitude'              => $this->nullIf( $result->latitude, '' ),
                'longitude'             => $this->nullIf( $result->longitude, '' ),
                'status_antrean'        => $status_antrean,
            ];

            RiwayatAntrean::create($data);

            JadwalPasien::where('id_poli', '=', $id_poli)
                        ->where('tgl_pelayanan', '=', $CURRENT_DATE)
                        ->where('id_pasien', '=', $id_pasien)
                        ->delete();
        }
    }

    public function insertAntrean ( Request $request )
    {
        date_default_timezone_set('Asia/Jakarta');
        $CURRENT_DATE = date('Y-m-d', strtotime('now'));

        $hari           = $request->input('hari');
        $id_poli        = $request->input('id_poli');
        $id_pasien      = $request->input('id_pasien');
        $tipe_booking   = $request->input('tipe_booking');
        $jenis_pasien   = $request->input('jenis_pasien');
        $latitude       = $request->input('latitude');
        $longitude      = $request->input('longitude');

        // Jika sudah mengambil Antrean
        if ( $this->isAmbilAntrean($id_pasien) ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Anda masih memiliki antrean berlangsung!',
                'data'      => ''
            ], Response::HTTP_CONFLICT);
        }

        // Jika Bukan Booking
        if ( $tipe_booking == 0 ) {
            // Jika Poliklinik aktif.
            if ( $this->isPoliklinikAktif($id_poli) ) {
                // Proses Antrean
                if( $this->kuotaNonBooking($hari, $id_poli, $id_pasien, $latitude, $longitude, $jenis_pasien) ) {
                    $this->sortNumber($id_poli, $CURRENT_DATE);
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Kuota pada hari ini tidak tersedia!',
                        'data'      => ''
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                // Jika Poliklinik tidak aktif.
                // Tampilan pesan gagal.
                return response()->json([
                    'success'   => false,
                    'message'   => 'Poliklinik tidak aktif!',
                    'data'      => ''
                ], Response::HTTP_CONFLICT);
            }
        } else {
            // Jika Booking
            // Jika Poliklinik memiliki jadwal di waktu yang dipilih.
            $tgl_pelayanan = $request["tgl_pelayanan"];
            $jam_booking = $request["jam_booking"];
            if ( $this->isJadwalTersedia($id_poli, $hari, $jam_booking) ) {
                // Proses Antrean
                if ( $this->kuotaBooking($hari, $id_poli, $id_pasien, $jenis_pasien, $tgl_pelayanan, $jam_booking, $latitude, $longitude) ) {
                    $this->sortNumber($id_poli, $tgl_pelayanan);
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Kuota untuk jam yang anda pilih tidak tersedia!',
                        'data'      => ''
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                // Jika tidak memiliki waktu
                // Tampilan pesan gagal
                return response()->json([
                    'success'   => false,
                    'message'   => 'Jadwal yang anda pilih tidak sesuai!',
                    'data'      => ''
                ], Response::HTTP_CONFLICT);
            }
        }
    }

    public function insertAntreanNormal(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $tipe_booking       = 0;
        $hari               = $request->input('hari');
        $id_poli            = $request->input('id_poli');
        $jenis_pasien       = $request->input('jenis_pasien');

        $nama_lengkap       = $request->input('nama_lengkap');
        $tgl_lahir          = $request->input('tgl_lahir');
        $alamat             = $request->input('alamat');
        $latitude           = $request->input('latitude');
        $longitude          = $request->input('longitude');
        $kepala_keluarga    = $request->input('kepala_keluarga');
        $no_handphone       = $request->input('no_handphone');

        $data = [
            'username'        => NULL,
            'no_handphone'    => $this->nullIf( $no_handphone, '' ),
            'password'        => NULL,
            'kepala_keluarga' => $this->nullIf( $kepala_keluarga, '' ),
            'tgl_lahir'       => $this->nullIf( $tgl_lahir, '' ),
            'alamat'          => $this->nullIf( $alamat, '' ),
            'latitude'        => NULL,
            'longitude'       => NULL,
            'nama_lengkap'    => $this->nullIf( $nama_lengkap, '' ),
            'api_token'       => NULL,
            'jenis_pasien'    => $this->nullIf( $jenis_pasien, '' ),
        ];

        $pasien = Pasien::create($data);
        $id_pasien = $pasien->id_pasien;

        // Jika Poliklinik aktif.
        if ( $this->isPoliklinikAktif($id_poli) ) {
            // Proses Antrean
            if( $this->kuotaNonBooking($hari, $id_poli, $id_pasien, $latitude, $longitude, $jenis_pasien) ) {
                $this->sortNumber($id_poli, $CURRENT_DATE);
                return response()->json([
                    'success'   => true,
                    'message'   => 'Berhasil!',
                    'data'      => ''
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success'   => true,
                    'message'   => 'Kuota pada hari ini tidak tersedia!',
                    'data'      => ''
                ], Response::HTTP_CONFLICT);
            }
        } else {
            // Jika Poliklinik tidak aktif.
            // Tampilan pesan gagal.
            return response()->json([
                'success'   => false,
                'message'   => 'Poliklinik tidak aktif!',
                'data'      => ''
            ], Response::HTTP_CONFLICT);
        }
    }

    public function insertAntreanGawat(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME       = date("H:i", strtotime("now"));
        $CURRENT_DATE       = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE   = date("Y-m-d H:i", strtotime("now"));
        $jamIterator        = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));

        $hari               = $request->input('hari');
        $id_poli            = $request->input('id_poli');
        $jenis_pasien       = $request->input('jenis_pasien');

        $nama_lengkap       = $request->input('nama_lengkap');
        $tgl_lahir          = $request->input('tgl_lahir');
        $alamat             = $request->input('alamat');
        $kepala_keluarga    = $request->input('kepala_keluarga');
        $no_handphone       = $request->input('no_handphone');

        $resultInfoPoliklinik = Jadwal::with('poliklinik')->where([
            ['hari'     , '=', $hari],
            ['id_poli'  , '=', $id_poli],
        ])->first();

        $rataRata           = $resultInfoPoliklinik->poliklinik->rerata_waktu_pelayanan;

        while (date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator))) {
            $jamIterator    = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
        }

        $data = [
            'username'        => NULL,
            'no_handphone'    => $this->nullIf( $no_handphone, '' ),
            'password'        => NULL,
            'kepala_keluarga' => $this->nullIf( $kepala_keluarga, '' ),
            'tgl_lahir'       => $this->nullIf( $tgl_lahir, '' ),
            'alamat'          => $this->nullIf( $alamat, '' ),
            'latitude'        => NULL,
            'longitude'       => NULL,
            'nama_lengkap'    => $this->nullIf( $nama_lengkap, '' ),
            'api_token'       => NULL,
            'jenis_pasien'    => $this->nullIf( $jenis_pasien, '' ),
        ];

        $pasien             = Pasien::create($data);
        $id_pasien          = $pasien->id_pasien;

        $data = [
            'id_poli'               => $id_poli,
            'hari'                  => $hari,
            'id_pasien'             => $id_pasien,
            'nomor_antrean'         => 0,
            'tipe_booking'          => 0,
            'tgl_pelayanan'         => $CURRENT_DATE,
            'jam_booking'           => $jamIterator,
            'waktu_daftar_antrean'  => $CURRENT_TIMEDATE,
            'jam_mulai_dilayani'    => $CURRENT_TIME,
            'jam_selesai_dilayani'  => NULL,
            'latitude'              => NULL,
            'longitude'             => NULL,
            'status_antrean'        => 2,
        ];

        JadwalPasien::create($data);
        $this->sortNumber($id_poli, $CURRENT_DATE);

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil!',
            'data'      => ''
        ], Response::HTTP_OK);

    }

    private function nullIf ( $expectValue, $actualValue )
    {
        if ( $expectValue == $actualValue ){
            return NULL;
        } else {
            return $expectValue;
        }
    }

    // Kebutuhan Insert Booking
    private function isJadwalTersedia ( string $id_poli, string $hari, string $jam_booking ): bool
    {
        $resultCheckRegist = Jadwal::with('poliklinik')
            ->where('hari', '=', $hari)
            ->where('jam_buka_booking', '<=', $jam_booking)
            ->where('jam_tutup_booking', '>=', $jam_booking)
            ->where('id_poli', '=', $id_poli)->first();

        return ( $resultCheckRegist->poliklinik );
    }

    private function kuotaBooking (
        tring $hari,
        int $id_poli,
        int $id_pasien,
        int $jenis_pasien,
        string $tgl_pelayanan,
        string $jam_booking,
        string $latitude,
        string $longitude ): bool
    {
        date_default_timezone_set("Asia/Jakarta");

        // Inisialisasi
        $CURRENT_TIMEDATE       = date("Y-m-d H:i:s", strtotime("now"));
        $jamBookingIterator     = $jam_booking;
        $jam                    = substr($jamBookingIterator, 0, 2) . '%';
        $resultInfoPoliklinik   = Poliklinik::where('id_poli', '=', $id_poli)->first();
        $resultAntrean          = JadwalPasien::where('id_poli', '=', $id_poli)
                                              ->where('tgl_pelayanan', '=', $tgl_pelayanan)
                                              ->where('jam_booking', 'LIKE', $jam)->get();

        $antrean = 0;
        if ( !$resultAntrean->isEmpty() ) {
            $antrean = count($resultAntrean);
        }

        $status = false;
        $rataRata = $resultInfoPoliklinik->rerata_waktu_pelayanan;
        $kuota = floor(60/$rataRata);

        while ( ($status == false) && (substr($jamBookingIterator, 0, 2) == substr($jam_booking, 0, 2)) ) {
            $result = JadwalPasien::where([
                ['id_poli', '=', $id_poli],
                ['tgl_pelayanan', '=', $tgl_pelayanan],
                ['jam_booking', '=', $jamBookingIterator],
                ['status_antrean', '!=', 5]
            ])->get();

            if( $result->isEmpty() ){
                $status = true;
            }

            if ( $status != true ) {
                $jamBookingIterator = date("H:i", strtotime($jamBookingIterator . ' + ' . $rataRata . ' minutes'));
            }
        }

        if( $status ) {
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
                'latitude'              => $this->nullIf( $latitude, '' ),
                'longitude'             => $this->nullIf( $longitude, '' ),
                'status_antrean'        => 1,
            ];
            JadwalPasien::create($data);

            return true;
        } else {
            return false;
        }
    }

    // Sort Nomor Antrean
    private function sortNumber ( int $id_poli, string $tgl_pelayanan )
    {
        $result = JadwalPasien::where([
            ['id_poli', '=', $id_poli],
            ['tgl_pelayanan', '=' , $tgl_pelayanan],
        ])->orderBy('jam_booking', 'asc')->orderBy('waktu_daftar_antrean', 'asc')->get();

        $i = 0;
        while ( $i < count($result) ) {
            $nomor                  = $i + 1;
            $waktu_daftar_antrean   = $result[$i]->waktu_daftar_antrean;
            $idPasien               = $result[$i]->id_pasien;

            JadwalPasien::where('id_poli', '=', $id_poli)
                ->where('id_pasien', '=', $idPasien)
                ->where('waktu_daftar_antrean', '=', $waktu_daftar_antrean)
                ->where('tgl_pelayanan', '=', $tgl_pelayanan)
                ->update(['nomor_antrean' => $nomor]);
            $i++;
        }
    }

    private function isAmbilAntrean ( int $id_pasien ): bool
    {
        $resultCheckRegist = JadwalPasien::where('id_pasien', '=', $id_pasien)
            ->where('status_antrean', '!=', 3)
            ->where('status_antrean', '!=', 5)
            ->get();

        return ( !$resultCheckRegist->isEmpty() );
    }

    private function isPoliklinikAktif ( int $id_poli ): bool
    {
        $resultCheckRegist = Poliklinik::where('id_poli', '=', $id_poli)
            ->where('status_poli', '=', 1)
            ->get();

        return ( $resultCheckRegist );
    }

    private function kuotaNonBooking (
        string $hari,
        int $id_poli,
        int $id_pasien,
        string $latitude,
        string $longitude,
        int $jenis_pasien): bool
    {
        date_default_timezone_set("Asia/Jakarta");
        $status = false;
        $CURRENT_DATE           = date("Y-m-d", strtotime("now"));
        $CURRENT_TIME           = date("H:i", strtotime("now"));
        $CURRENT_TIMEDATE       = date("Y-m-d H:i", strtotime("now"));
        $jamIterator            = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));
        $resultInfoPoliklinik   = Jadwal::with('poliklinik')
                                        ->where('hari', '=', $hari)
                                        ->where('id_poli', '=', $id_poli)
                                        ->first();

        if($resultInfoPoliklinik != null){
            $rataRata   = $resultInfoPoliklinik->poliklinik->rerata_waktu_pelayanan;
            $jamTutup   = $resultInfoPoliklinik->jam_tutup_booking;
            $jamBuka    = $resultInfoPoliklinik->jam_buka_booking;
            $kuota      = floor(60/$rataRata);

            while ( date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator)) ) {
                $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
            }

            while ( ( $status == false ) AND ( $jamIterator < $jamTutup ) AND ( $jamIterator >= $jamBuka ) ) {
                $result = JadwalPasien::where('id_poli', '=', $id_poli)
                    ->where('tgl_pelayanan', '=', $CURRENT_DATE)
                    ->where('jam_booking', '=', $jamIterator)
                    ->where('status_antrean', '!=', 5)
                    ->get();

                if ( $result->isEmpty() ) {
                    $status = true;
                }

                if ( $status != true ) {
                    $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
                }
            }
        } else {
            $status = false;
        }

        if ( $status ) {
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
                'latitude'              => $this->nullIf( $latitude, '' ),
                'longitude'             => $this->nullIf( $longitude, '' ),
                'status_antrean'        => 1,
            ];
            JadwalPasien::create($data);
            return true;
        } else {
            return false;
        }
    }
}
