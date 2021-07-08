<?php

namespace App\Http\Controllers;

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
    public function getEstimasiAntrean ( Request $request )
    {
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));

        $id_poli        = $request->input('id_poli');
        $tgl_pelayanan  = $request->input('tgl_pelayanan');
        $jam_booking    = $request->input('jam_booking');

        $antreanDiatas  = JadwalPasien::find($id_poli)->where([
            ['tgl_pelayanan' , '=' , $tgl_pelayanan],
            ['jam_booking'   , '<' , $jam_booking]
        ])->where( function($q) {
            $q->where('status_antrean'  , '='  , 1)
              ->orWhere('status_antrean', '='  , 4);
        })->get();

        $resultInfoPoliklinik   = Poliklinik::find($id_poli);
        $rataRata               = $resultInfoPoliklinik->rerata_waktu_pelayanan;

        $estimasiAntrean = 0;
        $jam_booking_top = null;
        if ( count($antreanDiatas) != 0 ) {
            $estimasiAntrean = count($antreanDiatas) * $rataRata;
            $jam_booking_top = $antreanDiatas[0]->jam_booking;
        }

        if ( $jam_booking_top == null ) {
            return response()->json($jam_booking, Response::HTTP_OK);
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

    public function getInfoAntrean () {
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

    public function getInfoAntreanWithPasienId ( Request $request, $id )
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

    public function getInfoAntreanWithPoliId ( Request $request, $id )
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

    public function getRiwayatAntreanWithPasienId ( Request $request, $id )
    {
        $result = RiwayatAntrean::with('pasien', 'poliklinik')->where('id_pasien', '=', $id)->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getRiwayatAntreanWithPoliId ( Request $request, $id )
    {
        $result = RiwayatAntrean::with('pasien', 'poliklinik')->where('id_poli', '=', $id)->get();

        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getInfoAntreanSementaraWithPoliId ( Request $request, $id )
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

    public function getInfoAntreanSelesaiWithPoliId ( Request $request, $id )
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
                )->orderBy('jam_booking', 'asc')->get();

            $data = [
                'id_poli'               => $id_poli,
                'id_pasien'             => $id_pasien,
                'nomor_antrean'         => $this->nullIf( $result[0]->nomor_antrean, '' ),
                'tipe_booking'          => $result[0]->tipe_booking,
                'tgl_pelayanan'         => $result[0]->tgl_pelayanan,
                'jam_booking'           => $this->nullIf( $result[0]->jam_booking, '' ),
                'waktu_daftar_antrean'  => $result[0]->waktu_daftar_antrean,
                'jam_mulai_dilayani'    => $this->nullIf( $result[0]->jam_mulai_dilayani, '' ),
                'jam_selesai_dilayani'  => $this->nullIf( $result[0]->jam_selesai_dilayani, '' ),
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

        // Jika sudah mengambil Antrean
        if ( $this->isSudahAmbilAntrean($id_pasien) ) {
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
                if( $this->hasKuotaNonBooking($hari, $id_poli, $id_pasien, $jenis_pasien) ) {
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
                if ( $this->hasKuotaBooking($hari, $id_poli, $id_pasien, $jenis_pasien, $tgl_pelayanan, $jam_booking) ) {
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

    public function insertAntreanByAdmin(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));

        $tipe_booking       = 0;
        $hari               = $request->input('hari');
        $id_poli            = $request->input('id_poli');
        $jenis_pasien       = $request->input('jenis_pasien');

        $nama_lengkap       = $request->input('nama_lengkap');
        $tgl_lahir          = $request->input('tgl_lahir');
        $alamat             = $request->input('alamat');
        $kepala_keluarga    = $request->input('kepala_keluarga');
        $no_handphone       = $request->input('no_handphone');

        $data = [
            'username'        => NULL,
            'no_handphone'    => $this->nullIf( $no_handphone, '' ),
            'kepala_keluarga' => $this->nullIf( $kepala_keluarga, '' ),
            'tgl_lahir'       => $this->nullIf( $tgl_lahir, '' ),
            'alamat'          => $this->nullIf( $alamat, '' ),
            'nama_lengkap'    => $this->nullIf( $nama_lengkap, '' ),
            'password'        => NULL,
            'api_token'       => NULL,
            'jenis_pasien'    => $this->nullIf( $jenis_pasien, '' ),
        ];

        $pasien = Pasien::create($data);
        $id_pasien = $pasien->id_pasien;

        // Jika Poliklinik aktif.
        if ( $this->isPoliklinikAktif($id_poli) ) {
            // Proses Antrean
            if( $this->hasKuotaNonBooking($hari, $id_poli, $id_pasien, $jenis_pasien) ) {
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

        $resultInfoPoliklinik = Poliklinik::with(['jadwal' => function ( $q ) use ( $id_poli, $hari ) {
            $q->where('hari', '=', $hari);
        }])->where('id_poli', '=', $id_poli)->get();

        $rataRata           = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;

        while (date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator))) {
            $jamIterator    = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
        }

        $data = [
            'username'        => NULL,
            'no_handphone'    => $this->nullIf( $no_handphone, '' ),
            'kepala_keluarga' => $this->nullIf( $kepala_keluarga, '' ),
            'tgl_lahir'       => $this->nullIf( $tgl_lahir, '' ),
            'alamat'          => $this->nullIf( $alamat, '' ),
            'nama_lengkap'    => $this->nullIf( $nama_lengkap, '' ),
            'password'        => NULL,
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
        $resultCheckRegist = Poliklinik::with([
            'jadwal' => function ( $q ) use ( $hari, $jam_booking ) {
                $q->where('hari', '=', $hari)
                    ->where('jam_buka_booking', '<=', $jam_booking)
                    ->where('jam_tutup_booking', '>=', $jam_booking);
            }
        ])->where('id_poli', '=', $id_poli)->get();

        return ( !$resultCheckRegist->isEmpty() );
    }

    private function hasKuotaBooking ( string $hari, int $id_poli, int $id_pasien, int $jenis_pasien, string $tgl_pelayanan, string $jam_booking ): bool
    {
        date_default_timezone_set("Asia/Jakarta");

        // Inisialisasi
        $CURRENT_TIMEDATE       = date("Y-m-d H:i:s", strtotime("now"));
        $jamBookingIterator     = $jam_booking;
        $jam                    = substr($jamBookingIterator, 0, 2) . '%';
        $resultInfoPoliklinik   = Poliklinik::where('id_poli', '=', $id_poli)->get();
        $resultAntrean          = JadwalPasien::where('id_poli', '=', $id_poli)
                                              ->where('tgl_pelayanan', '=', $tgl_pelayanan)
                                              ->where('jam_booking', 'LIKE', $jam)->get();

        $antrean = 0;
        if ( !$resultAntrean->isEmpty() ) {
            $antrean = count($resultAntrean);
        }

        $status = false;
        $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
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

    private function isSudahAmbilAntrean ( int $id_pasien ): bool
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

        return ( !$resultCheckRegist->isEmpty() );
    }

    private function hasKuotaNonBooking ( string $hari, int $id_poli, int $id_pasien, int $jenis_pasien ): bool
    {
        date_default_timezone_set("Asia/Jakarta");
        $status = false;
        $CURRENT_DATE           = date("Y-m-d", strtotime("now"));
        $CURRENT_TIME           = date("H:i", strtotime("now"));
        $CURRENT_TIMEDATE       = date("Y-m-d H:i", strtotime("now"));
        $jamIterator            = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));
        $resultInfoPoliklinik   = Poliklinik::with(['jadwal' => function($q) use ($id_poli, $hari) {
                                      $q->where('hari', '=', $hari);
                                  }])->where('id_poli', '=', $id_poli)->get();
        $resultAntrean          = JadwalPasien::where('id_poli', '=', $id_poli)
                                              ->where('tgl_pelayanan', '=', $CURRENT_DATE)->get();
        $rataRata               = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
        $jamTutup               = $resultInfoPoliklinik[0]->jadwal->jam_tutup_booking;
        $jamBuka                = $resultInfoPoliklinik[0]->jam_buka_booking;
        $kuota                  = floor(60/$rataRata);


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
                'status_antrean'        => 1,
            ];
            JadwalPasien::create($data);
            return true;
        } else {
            return false;
        }
    }
}
