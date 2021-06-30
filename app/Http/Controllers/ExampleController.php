<?php
namespace App\Http\Controllers;
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

        $id_poli = $request["id_poli"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $jam_booking = $request["jam_booking"];

        $antreanDiatas = DB::select("SELECT * FROM `jadwal_pasien` 
        WHERE id_poli='$id_poli' AND 
        tgl_pelayanan='$tgl_pelayanan' AND 
        jam_booking < '$jam_booking' AND 
        (status_antrean = 1 OR status_antrean = 4)
        ORDER BY jam_booking ASC");
        
        $resultInfoPoliklinik = DB::select("SELECT * FROM `poliklinik` WHERE id_poli='$id_poli'");
        $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;

        $estimasiAntrean = 0;
        $jam_booking_top = null;
        if($antreanDiatas != null){
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
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $resultPoli = DB::select("SELECT 
        COUNT(case when jadwal_pasien.tgl_pelayanan='$CURRENT_DATE' then 1 else null end) AS 'total_antrean',
        COUNT(case when (jadwal_pasien.status_antrean=4 AND jadwal_pasien.tgl_pelayanan='$CURRENT_DATE')  then 1 else null end) AS 'antrean_sementara', 
        MAX(case when (jadwal_pasien.status_antrean=2 AND jadwal_pasien.tgl_pelayanan='$CURRENT_DATE') then jadwal_pasien.nomor_antrean else 0 end) AS 'nomor_antrean',
        poliklinik.id_poli,
        poliklinik.status_poli, 
        poliklinik.nama_poli
            FROM poliklinik LEFT JOIN jadwal_pasien ON poliklinik.id_poli=jadwal_pasien.id_poli
            GROUP BY poliklinik.id_poli
            ORDER BY poliklinik.id_poli ASC;");

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
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_booking,
            jadwal_pasien.waktu_daftar_antrean,
            jadwal_pasien.jam_mulai_dilayani,
            jadwal_pasien.jam_selesai_dilayani,
            jadwal_pasien.status_antrean,
            jadwal_pasien.hari,
            p.id_poli,
            p.nama_poli,
            pa.id_pasien,
            pa.username,
            pa.no_handphone,
            pa.kepala_keluarga,
            pa.nama_lengkap,
            pa.alamat,
            pa.tgl_lahir,
            pa.jenis_pasien
        FROM jadwal_pasien 
        LEFT JOIN pasien pa ON jadwal_pasien.id_pasien=pa.id_pasien
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli 
        WHERE jadwal_pasien.id_poli='$id' AND 
        (jadwal_pasien.status_antrean=1 OR jadwal_pasien.status_antrean=2) AND 
        jadwal_pasien.tgl_pelayanan='$CURRENT_DATE' 
        ORDER BY jadwal_pasien.jam_booking ASC");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getRiwayatWithPoliId(Request $request, $id){
        $result = DB::select("SELECT * FROM riwayat_antrean WHERE id_poli='$id' ORDER BY jam_booking ASC");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getRiwayatWithPasienId(Request $request, $id){
        $result = DB::select("SELECT * FROM riwayat_antrean WHERE id_pasien='$id'");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanWithPasienId(Request $request, $id){
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_booking,
            jadwal_pasien.waktu_daftar_antrean,
            jadwal_pasien.jam_mulai_dilayani,
            jadwal_pasien.jam_selesai_dilayani,
            jadwal_pasien.status_antrean,
            jadwal_pasien.hari,
            p.id_poli,
            p.nama_poli,
            pa.id_pasien,
            pa.username,
            pa.no_handphone,
            pa.kepala_keluarga,
            pa.nama_lengkap,
            pa.alamat,
            pa.tgl_lahir,
            pa.jenis_pasien
        FROM jadwal_pasien 
        LEFT JOIN pasien pa ON jadwal_pasien.id_pasien=pa.id_pasien
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli 
        WHERE jadwal_pasien.id_pasien='$id' AND 
        (jadwal_pasien.status_antrean!=3 AND jadwal_pasien.status_antrean!=5)");
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
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_booking,
            jadwal_pasien.waktu_daftar_antrean,
            jadwal_pasien.jam_mulai_dilayani,
            jadwal_pasien.jam_selesai_dilayani,
            jadwal_pasien.status_antrean,
            jadwal_pasien.hari,
            p.id_poli,
            p.nama_poli,
            pa.id_pasien,
            pa.username,
            pa.no_handphone,
            pa.kepala_keluarga,
            pa.nama_lengkap,
            pa.alamat,
            pa.tgl_lahir,
            pa.jenis_pasien
        FROM jadwal_pasien 
        LEFT JOIN pasien pa ON jadwal_pasien.id_pasien=pa.id_pasien
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli 
        WHERE jadwal_pasien.id_poli='$id' AND 
        jadwal_pasien.status_antrean=4 AND 
        jadwal_pasien.tgl_pelayanan='$CURRENT_DATE' 
        ORDER BY jadwal_pasien.jam_booking ASC");
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
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_booking,
            jadwal_pasien.waktu_daftar_antrean,
            jadwal_pasien.jam_mulai_dilayani,
            jadwal_pasien.jam_selesai_dilayani,
            jadwal_pasien.status_antrean,
            jadwal_pasien.hari,
            p.id_poli,
            p.nama_poli,
            pa.id_pasien,
            pa.username,
            pa.no_handphone,
            pa.kepala_keluarga,
            pa.nama_lengkap,
            pa.alamat,
            pa.tgl_lahir,
            pa.jenis_pasien
        FROM jadwal_pasien 
        LEFT JOIN pasien pa ON jadwal_pasien.id_pasien=pa.id_pasien
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli 
        WHERE jadwal_pasien.id_poli='$id' AND 
        (jadwal_pasien.status_antrean=3 OR jadwal_pasien.status_antrean=5) AND 
        jadwal_pasien.tgl_pelayanan='$CURRENT_DATE' 
        ORDER BY jadwal_pasien.jam_booking ASC");
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
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $id_poli = $request["id_poli"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $id_pasien = $request["id_pasien"];
        $status_antrean = $request["status_antrean"];

        if($status_antrean == 2){
            DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean', jam_mulai_dilayani = '$CURRENT_TIME'
                WHERE id_poli = '$id_poli' AND tgl_pelayanan='$tgl_pelayanan' AND id_pasien = '$id_pasien'");
        } else if($status_antrean == 3){
            DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean', jam_selesai_dilayani = '$CURRENT_TIME'
                WHERE id_poli = '$id_poli' AND tgl_pelayanan='$tgl_pelayanan' AND id_pasien = '$id_pasien'");
        } else {
            DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean'
                WHERE id_poli = '$id_poli' AND tgl_pelayanan='$tgl_pelayanan' AND id_pasien = '$id_pasien'");
        }
        
        // Jika status selesai / cancel. Langsung dipindah ke entitas Riwayat
        if(($status_antrean == 5) || ($status_antrean == 3)){
            $result = DB::select("SELECT * FROM jadwal_pasien 
                LEFT JOIN pasien pa ON jadwal_pasien.id_pasien=pa.id_pasien
                LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli 
                WHERE jadwal_pasien.id_poli='$id_poli' AND 
                jadwal_pasien.tgl_pelayanan='$tgl_pelayanan' AND 
                jadwal_pasien.id_pasien='$id_pasien'");
                
            $nomor_antrean = $result[0]->nomor_antrean;
            $tipe_booking = $result[0]->tipe_booking;
            $jam_booking =$result[0]->jam_booking;
            $waktu_daftar_antrean =$result[0]->waktu_daftar_antrean;
            $jam_mulai_dilayani =$result[0]->jam_mulai_dilayani;
            $jam_selesai_dilayani =$result[0]->jam_selesai_dilayani;

            DB::insert("INSERT INTO riwayat_antrean VALUES(
                0, '$id_poli', '$id_pasien', NULLIF('$nomor_antrean',''),
                '$tipe_booking', '$tgl_pelayanan', NULLIF('$jam_booking',''), '$waktu_daftar_antrean', 
                NULLIF('$jam_mulai_dilayani',''),NULLIF('$jam_selesai_dilayani',''), '$status_antrean')");

            DB::delete("DELETE FROM jadwal_pasien WHERE id_poli='$id_poli' AND tgl_pelayanan='$CURRENT_DATE' AND id_pasien='$id_pasien'");
        }
        
    }

    // Kebutuhan Insert Bukan Booking

    private function isAmbilAntrean(int $id_pasien){
        $resultCheckRegist = DB::select("SELECT * FROM `jadwal_pasien` 
        WHERE id_pasien = '$id_pasien' AND (status_antrean!=3 AND status_antrean!=5)");
        return ($resultCheckRegist != null);
    }

    private function isPoliklinikAktif(int $id_poli){
        $resultCheckRegist = DB::select("SELECT * FROM `poliklinik` 
        WHERE id_poli = '$id_poli' AND status_poli = 1");
        return ($resultCheckRegist != null);
    }

    private function kuotaNonBooking(
        string $hari,
        int $id_poli, 
        int $id_pasien,
        int $jenis_pasien){
            date_default_timezone_set("Asia/Jakarta");
            $status = false;
            $CURRENT_DATE = date("Y-m-d", strtotime("now"));
            $CURRENT_TIME = date("H:i", strtotime("now"));
            $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));
            $jamIterator = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));
            $resultInfoPoliklinik = DB::select("SELECT * FROM `poliklinik` JOIN `jadwal` ON poliklinik.id_poli=jadwal.id_poli WHERE poliklinik.id_poli='$id_poli' AND jadwal.hari='$hari'");
            $resultAntrean = DB::select("SELECT * FROM `jadwal_pasien` WHERE id_poli='$id_poli' AND tgl_pelayanan='$CURRENT_DATE'");
            $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
            $jamTutup = $resultInfoPoliklinik[0]->jam_tutup_booking;
            $jamBuka = $resultInfoPoliklinik[0]->jam_buka_booking;
            $kuota = floor(60/$rataRata);

            
            while (date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator))) {
                $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
            }

            while (($status == false) AND ($jamIterator < $jamTutup) AND ($jamIterator >= $jamBuka)){
                $result = DB::select("SELECT * FROM `jadwal_pasien` WHERE id_poli='$id_poli' AND tgl_pelayanan='$CURRENT_DATE' AND jam_booking='$jamIterator' AND status_antrean!=5");
                if($result == null){
                    $status = true;
                }
                if($status != true){
                    $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
                }
            }

            if($status){
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    '0', '0', '$CURRENT_DATE', '$jamIterator', '$CURRENT_TIMEDATE',
                    NULL, NULL, 1)");
                return true;
            } else {
                return false;
            }

            

            

    }

    // Kebutuhan Insert Booking
    private function isJadwalTersedia(string $id_poli, string $hari, string $jam_booking){
        $resultCheckRegist = DB::select("SELECT * FROM `poliklinik` 
        JOIN jadwal ON poliklinik.id_poli=jadwal.id_poli 
        WHERE poliklinik.id_poli = '$id_poli' AND 
        jadwal.hari = '$hari' AND 
        ('$jam_booking' >= jadwal.jam_buka_booking AND '$jam_booking' <= jadwal.jam_tutup_booking)");
        return ($resultCheckRegist != null);
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
            $resultInfoPoliklinik = DB::select("SELECT * FROM `poliklinik` WHERE id_poli='$id_poli'");
            $resultAntrean = DB::select("SELECT * FROM `jadwal_pasien` WHERE id_poli='$id_poli' AND tgl_pelayanan='$tgl_pelayanan' AND jam_booking LIKE '$jam'");
            $antrean = 0;
            if($resultAntrean != null){
                $antrean = count($resultAntrean);
            }
            $status = false;
            $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;
            $kuota = floor(60/$rataRata);
            
            while (($status == false) && (substr($jamBookingIterator, 0, 2) == substr($jam_booking, 0, 2)) ) {
                $result = DB::select("SELECT * FROM `jadwal_pasien` WHERE id_poli='$id_poli' AND tgl_pelayanan='$tgl_pelayanan' AND jam_booking='$jamBookingIterator' AND status_antrean!=5");
                if($result == null){
                    $status = true;
                }
                if($status != true){
                    $jamBookingIterator = date("H:i", strtotime($jamBookingIterator . ' + ' . $rataRata . ' minutes'));
                }
            }

            if($status){
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    '0', '1', '$tgl_pelayanan', '$jamBookingIterator', '$CURRENT_TIMEDATE',
                    NULL, NULL, 1)");
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

    public function insertAntreanNormal(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME = date("H:i", strtotime("now"));
        $CURRENT_DATE = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE = date("Y-m-d H:i", strtotime("now"));

        $tipe_booking       = 0;
        $hari               = $request["hari"];
        $id_poli            = $request["id_poli"];
        $jenis_pasien       = $request["jenis_pasien"];

        $nama_lengkap       = $request["nama_lengkap"];
        $tgl_lahir          = $request["tgl_lahir"];
        $alamat             = $request["alamat"];
        $kepala_keluarga    = $request["kepala_keluarga"];
        $no_handphone       = $request["no_handphone"];

        DB::insert("INSERT INTO `pasien` VALUES (0, NULL, NULLIF('$no_handphone',''), NULLIF('$kepala_keluarga',''), NULLIF('$tgl_lahir',''), NULLIF('$alamat',''), NULLIF('$nama_lengkap',''), NULL, NULL, NULLIF('$jenis_pasien',''))");
        $resultId = DB::select("SELECT LAST_INSERT_ID() AS id_pasien");
        $id_pasien = $resultId[0]->id_pasien;

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
        
    }

    public function insertAntreanGawat(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_TIME       = date("H:i", strtotime("now"));
        $CURRENT_DATE       = date("Y-m-d", strtotime("now"));
        $CURRENT_TIMEDATE   = date("Y-m-d H:i", strtotime("now"));
        $jamIterator = date("H:i", strtotime(substr($CURRENT_TIME, 0, 2) . ':00'));

        $hari               = $request["hari"];
        $id_poli            = $request["id_poli"];
        $jenis_pasien       = $request["jenis_pasien"];

        $nama_lengkap       = $request["nama_lengkap"];
        $tgl_lahir          = $request["tgl_lahir"];
        $alamat             = $request["alamat"];
        $kepala_keluarga    = $request["kepala_keluarga"];
        $no_handphone       = $request["no_handphone"];

        $resultInfoPoliklinik = DB::select("SELECT * FROM `poliklinik` JOIN `jadwal` ON poliklinik.id_poli=jadwal.id_poli WHERE poliklinik.id_poli='$id_poli' AND jadwal.hari='$hari'");
        $rataRata = $resultInfoPoliklinik[0]->rerata_waktu_pelayanan;

        while (date("H:i", strtotime($CURRENT_TIME)) > date("H:i", strtotime($jamIterator))) {
            $jamIterator = date("H:i", strtotime($jamIterator . ' + ' . $rataRata . ' minutes'));
        }

        DB::insert("INSERT INTO `pasien` VALUES (0, NULL, NULLIF('$no_handphone',''), NULLIF('$kepala_keluarga',''), NULLIF('$tgl_lahir',''), NULLIF('$alamat',''), NULLIF('$nama_lengkap',''), NULL, NULL, NULLIF('$jenis_pasien',''))");
        $resultId = DB::select("SELECT LAST_INSERT_ID() AS id_pasien");
        $id_pasien = $resultId[0]->id_pasien;
        DB::insert("INSERT INTO jadwal_pasien VALUES('$id_poli', '$hari', '$id_pasien', '0', '0', '$CURRENT_DATE', '$jamIterator', '$CURRENT_TIMEDATE', '$CURRENT_TIME', NULL, 2)");
        $this->sortNumber($id_poli, $CURRENT_DATE);

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil!',
            'data'      => ''
        ], 200);

    }

    // Sort Nomor Antrean

    private function sortNumber(int $id_poli, string $tgl_pelayanan){
        $result = DB::select("SELECT * FROM jadwal_pasien 
        WHERE id_poli='$id_poli' AND tgl_pelayanan='$tgl_pelayanan' 
        ORDER BY jam_booking ASC, waktu_daftar_antrean ASC");

        $i = 0;
        while ($i < count($result)) {
            $nomor = $i+1;
            $waktu_daftar_antrean = $result[$i]->waktu_daftar_antrean;
            $idPasien = $result[$i]->id_pasien;
            DB::update("UPDATE jadwal_pasien SET nomor_antrean='$nomor' 
            WHERE 
            id_poli='$id_poli' AND 
            id_pasien='$idPasien' AND 
            waktu_daftar_antrean='$waktu_daftar_antrean' AND
            tgl_pelayanan='$tgl_pelayanan'");
            $i++;
        }

    }


    // Poliklinik
    public function getAllPoliklinik(){
        $result = [];
        $resultPoli = DB::select("SELECT * FROM poliklinik");

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i] = $row;
            $idPoli = $row->id_poli;
            $resultJadwal = DB::select("SELECT * FROM jadwal WHERE id_poli='$idPoli'");
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
        $resultPoli = DB::select("SELECT * FROM poliklinik WHERE id_poli='$id'");

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i] = $row;
            $idPoli = $row->id_poli;
            $resultJadwal = DB::select("SELECT * FROM jadwal WHERE id_poli='$idPoli'");
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
            DB::insert("INSERT INTO jadwal SET 
                hari='$hari', 
                jam_buka_booking='$jam_buka_booking', 
                jam_tutup_booking='$jam_tutup_booking',
                id_poli = (SELECT id_poli FROM poliklinik WHERE nama_poli='$nama_poli' LIMIT 1)");
        }
    }

    public function ubahPoliklinik(Request $request, $id){
        $id_poli = $id;
        $nama_poli = $request["nama_poli"];
        $desc_poli = $request["desc_poli"];
        $status_poli = $request["status_poli"];
        $rerata = $request["rerata_waktu_pelayanan"];

        DB::update("UPDATE poliklinik SET nama_poli = '$nama_poli',
        desc_poli = '$desc_poli', status_poli = '$status_poli',
        rerata_waktu_pelayanan = '$rerata' WHERE id_poli = '$id_poli'");

        DB::delete("DELETE FROM jadwal WHERE id_poli = '$id'");

        $jadwal = $request["jadwal"];
        foreach ($jadwal as $jadwalPerHari) {
            $hari = $jadwalPerHari["hari"];
            $jam_buka_booking = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking = $jadwalPerHari["jam_tutup_booking"];
            DB::insert("INSERT INTO jadwal SET 
                hari='$hari', 
                jam_buka_booking='$jam_buka_booking', 
                jam_tutup_booking='$jam_tutup_booking',
                id_poli = '$id_poli'");
        }
    }

    public function ubahStatusAllPoli(Request $request){
        $i=0;
        while($request[$i] != null){
            $id = $request[$i]["id_poli"];
            $status = $request[$i]["status_poli"];
            DB::update("UPDATE poliklinik SET status_poli = '$status' WHERE id_poli = '$id'");
            $i++;
        }



        if($i != 0){
            return response()->json($i, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function deletePoliklinik($id){
        DB::delete("DELETE FROM jadwal WHERE id_poli = '$id'");
        DB::delete("DELETE FROM poliklinik WHERE id_poli = '$id'");
    }
    


    // Perawat.
    public function getAllPerawat(){
        $result = DB::select("SELECT pe.id_perawat, pe.username, pe.password, pe.nama, pe.id_poli, po.nama_poli 
        FROM perawat pe 
        LEFT JOIN poliklinik po ON pe.id_poli=po.id_poli");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function insertPerawat(Request $request){
        $username = $request["username"];
        $password = $request["password"];
        $nama = $request["nama"];
        $id_poli = $request["id_poli"];
        DB::insert("INSERT perawat VALUES(0,'$username', '$password', '$nama', '$id_poli')");
    }

    public function editPerawat(Request $request, $id){
        $username = $request["username"];
        $password = $request["password"];
        $nama = $request["nama"];
        $id_poli = $request["id_poli"];
        DB::update("UPDATE perawat 
            SET 
                username = '$username',
                password = '$password',
                nama = '$nama',
                id_poli = '$id_poli'
             WHERE id_perawat = '$id'");
    }

    public function deletePerawat($id){
        DB::delete("DELETE FROM perawat WHERE id_perawat = '$id'");
    }

    public function getPerawat($id){
        $result = DB::select("SELECT pe.id_perawat, pe.username, pe.password, pe.nama, pe.id_poli, po.nama_poli 
        FROM perawat pe 
        LEFT JOIN poliklinik po ON pe.id_poli=po.id_poli
        WHERE pe.id_perawat='$id'");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function loginPerawat(Request $request){
        $username = $request["username"];
        $password = $request["password"];
        $result = DB::select("SELECT * FROM perawat WHERE username = '$username' AND password = '$password'");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

}
