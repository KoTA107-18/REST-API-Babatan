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

    // Antrean

    public function getAntreanInfo(){
        /*
        $resultPoli = DB::select("SELECT 
            COUNT(jadwal_pasien.status_antrean) AS 'total_antrean',
            COUNT(case jadwal_pasien.status_antrean when 4 then 1 else null end) AS 'antrean_sementara', 
            MAX(case jadwal_pasien.status_antrean when 2 then jadwal_pasien.nomor_antrean else 0 end) AS 'nomor_antrean',
            poliklinik.id_poli,
            poliklinik.status_poli, 
            poliklinik.nama_poli
                FROM poliklinik LEFT JOIN jadwal_pasien ON poliklinik.id_poli=jadwal_pasien.id_poli
                WHERE (jadwal_pasien.tgl_pelayanan=CURRENT_DATE() OR jadwal_pasien.tgl_pelayanan IS NULL)
                GROUP BY poliklinik.id_poli
                ORDER BY poliklinik.id_poli ASC;");*/

        $resultPoli = DB::select("SELECT 
        COUNT(case when jadwal_pasien.tgl_pelayanan=CURRENT_DATE() then 1 else null end) AS 'total_antrean',
        COUNT(case when (jadwal_pasien.status_antrean=4 AND jadwal_pasien.tgl_pelayanan=CURRENT_DATE())  then 1 else null end) AS 'antrean_sementara', 
        MAX(case when (jadwal_pasien.status_antrean=2 AND jadwal_pasien.tgl_pelayanan=CURRENT_DATE()) then jadwal_pasien.nomor_antrean else 0 end) AS 'nomor_antrean',
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
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_daftar_antrean,
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
        jadwal_pasien.tgl_pelayanan=CURRENT_DATE()");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getRiwayatWithPoliId(Request $request, $id){
        $result = DB::select("SELECT * FROM riwayat_antrean WHERE id_poli='$id'");
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
            jadwal_pasien.jam_daftar_antrean,
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
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_daftar_antrean,
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
        WHERE jadwal_pasien.id_poli='$id' AND jadwal_pasien.status_antrean=4 AND jadwal_pasien.tgl_pelayanan=CURRENT_DATE()");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanSelesaiWithPoliId(Request $request, $id){
        $result = DB::select("SELECT
            jadwal_pasien.nomor_antrean,
            jadwal_pasien.tipe_booking,
            jadwal_pasien.tgl_pelayanan,
            jadwal_pasien.jam_daftar_antrean,
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
        WHERE jadwal_pasien.id_poli='$id' AND (jadwal_pasien.status_antrean=3 OR jadwal_pasien.status_antrean=5) AND jadwal_pasien.tgl_pelayanan=CURRENT_DATE()");
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

        $id_poli = $request["id_poli"];
        $hari = $request["hari"];
        $id_pasien = $request["id_pasien"];
        $status_antrean = $request["status_antrean"];

        $result = DB::select("SELECT
        jadwal_pasien.nomor_antrean,
        jadwal_pasien.tipe_booking,
        jadwal_pasien.tgl_pelayanan,
        jadwal_pasien.jam_daftar_antrean,
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
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli WHERE 
        jadwal_pasien.id_poli='$id_poli' AND jadwal_pasien.hari='$hari' AND jadwal_pasien.id_pasien='$id_pasien'");
        
        // Jika status selesai / cancel. Langsung dipindah ke entitas Riwayat
        if(($status_antrean == 5) || ($status_antrean == 3)){
            // DB::delete("DELETE FROM jadwal_pasien WHERE id_poli='$id_poli' AND hari='$hari' AND id_pasien='$id_pasien'");
            $nomor_antrean = $result[0]->nomor_antrean;
            $tipe_booking = $result[0]->tipe_booking;
            $tgl_pelayanan =$result[0]->tgl_pelayanan;
            $jam_daftar_antrean =$result[0]->jam_daftar_antrean;
            $jam_mulai_dilayani =$result[0]->jam_mulai_dilayani;
            $jam_selesai_dilayani =$result[0]->jam_selesai_dilayani;
            $nama_poli =$result[0]->nama_poli;
            $username =$result[0]->username;
            $no_handphone =$result[0]->no_handphone;
            $kepala_keluarga =$result[0]->kepala_keluarga;
            $tgl_lahir =$result[0]->tgl_lahir;
            $alamat =$result[0]->alamat;
            $nama_lengkap =$result[0]->nama_lengkap;
            $jenis_pasien =$result[0]->jenis_pasien;

            DB::insert("INSERT INTO riwayat_antrean VALUES(
                0, '$id_poli', '$id_pasien', NULLIF('$nomor_antrean',''),
                '$tipe_booking', '$tgl_pelayanan', '$jam_daftar_antrean', 
                NULLIF('$jam_mulai_dilayani',''),NULLIF('$jam_selesai_dilayani',''), '$status_antrean',
                '$nama_poli', '$username', '$no_handphone',
                '$kepala_keluarga', '$tgl_lahir', '$alamat',
                '$nama_lengkap', NULLIF('$jenis_pasien',''))");
        }

        DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean'
                WHERE id_poli = '$id_poli' AND hari='$hari' AND $id_pasien='$id_pasien'");
        
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

    private function prosesInsert(
        string $hari, int $id_poli, int $id_pasien, int $tipe_booking, 
        int $jenis_pasien, string $tgl_pelayanan, string $jam_mulai_dilayani){

        /*
        BELUM_DILAYANI = 1;
        SEDANG_DILAYANI = 2;
        SUDAH_DILAYANI = 3;
        DILEWATI = 4;
        DIBATALKAN = 5; */
        $nomorAntrean;
        $antrean = DB::select("SELECT * FROM jadwal_pasien 
            WHERE tgl_pelayanan=CURRENT_DATE() AND id_poli='$id_poli'
            ORDER BY jam_daftar_antrean DESC LIMIT 2;");
        $dataPoliklinik = DB::select("SELECT * FROM `poliklinik` 
        JOIN jadwal ON poliklinik.id_poli=jadwal.id_poli 
        WHERE poliklinik.id_poli = '$id_poli' AND jadwal.hari = '$hari';");
        $antreanDiatas = null;
        $antreanDiatas1 = null;

        if(count($antrean) == 2){
            $antreanDiatas     = $antrean[0];
            $antreanDiatas1    = $antrean[1];
        } else if(count($antrean) == 1){
            $antreanDiatas     = $antrean[0];
        }

        // Jika diatasnya kosong.
        if($antreanDiatas == null){
            $nomorAntrean = 1;
            // Proses insert.
            if($tipe_booking == 0){
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    '$nomorAntrean', '$tipe_booking', CURRENT_DATE(), CURRENT_TIME(),
                    CURRENT_TIME(), NULL, 1)");
            } else {
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    '$nomorAntrean', '$tipe_booking', '$tgl_pelayanan', CURRENT_TIME(),
                    '$jam_mulai_dilayani', NULL, 1)");
            }
            return true;
        } else {
            // Jika diatasnya tipe booking.
            if($antreanDiatas->tipe_booking == 1){
                // Jika belum dilayani (1)
                if($antreanDiatas->status_antrean == 1){
                    $rerata         = $dataPoliklinik[0]->rerata_waktu_pelayanan;
                    $waktuSekarang  = strtotime(date("H:i", strtotime("now")));
                    $jamMulai1      = strtotime($antreanDiatas->jam_mulai_dilayani);
                    $jamMulai2      = strtotime($antreanDiatas1->jam_mulai_dilayani);
                    if($waktuSekarang > $jamMulai1){
                        $jamMulai1 = $waktuSekarang;
                    }
                    //menghitung selisih dengan hasil detik
                    $diff    =$jamMulai1 - $jamMulai2;
                    //membagi detik menjadi jam
                    $jam    =floor($diff / (60 * 60));
                    //membagi sisa detik setelah dikurangi $jam menjadi menit
                    $menit    =floor(($diff - $jam * (60 * 60)) / 60);

                    // Jika ada space
                    if($menit >= $rerata){
                        // Maka insert
                        $nomorAntrean = $antreanDiatas->nomor_antrean;
                        if($tipe_booking == 0){
                            DB::insert("INSERT INTO jadwal_pasien VALUES(
                                '$id_poli', '$hari', '$id_pasien',
                                '$nomor_antrean', '$tipe_booking', CURRENT_DATE(), CURRENT_TIME(),
                                CURRENT_TIME(), NULL, 1)");
                        } else {
                            DB::insert("INSERT INTO jadwal_pasien VALUES(
                                '$id_poli', '$hari', '$id_pasien',
                                '$nomor_antrean', '$tipe_booking', '$tgl_pelayanan', CURRENT_TIME(),
                                '$jam_mulai_dilayani', NULL, 1)");
                        }

                        $statusPoliDiatas = $antreanDiatas->status_antrean;
                        DB::update("UPDATE jadwal_antrean 
                        SET nomor_antrean = '$nomorAntrean-1'
                        WHERE id_poli = '$id_poli' AND 
                        hari='$hari' AND
                        status_antrean='$statusPoliDiatas'
                        tgl_pelayanan=CURRENT_DATE()");

                        return true;

                    } else {
                        // Jika tidak ada space
                        // Maka insert nomor antrean = 1
                        $nomorAntrean = $antreanDiatas->nomor_antrean + 1;
                        if($tipe_booking == 0){
                            DB::insert("INSERT INTO jadwal_pasien VALUES(
                                '$id_poli', '$hari', '$id_pasien',
                                '$nomor_antrean', '$tipe_booking', CURRENT_DATE(), CURRENT_TIME(),
                                CURRENT_TIME(), NULL, 1)");
                        } else {
                            DB::insert("INSERT INTO jadwal_pasien VALUES(
                                '$id_poli', '$hari', '$id_pasien',
                                '$nomor_antrean', '$tipe_booking', '$tgl_pelayanan', CURRENT_TIME(),
                                '$jam_mulai_dilayani', NULL, 1)");
                        }
                        return true;
                    }
                    
                } else {
                    // Jika 2,3,4,5 (sedang dilewat / dilayani / sudah dilayani / cancel)
                    // Maka insert
                    $nomorAntrean = $antreanDiatas->nomor_antrean + 1;
                    if($tipe_booking == 0){
                        DB::insert("INSERT INTO jadwal_pasien VALUES(
                            '$id_poli', '$hari', '$id_pasien',
                            '$nomor_antrean', '$tipe_booking', CURRENT_DATE(), CURRENT_TIME(),
                            CURRENT_TIME(), NULL, 1)");
                    } else {
                        DB::insert("INSERT INTO jadwal_pasien VALUES(
                            '$id_poli', '$hari', '$id_pasien',
                            '$nomor_antrean', '$tipe_booking', '$tgl_pelayanan', CURRENT_TIME(),
                            '$jam_mulai_dilayani', NULL, 1)");
                    }
                    return true;
                }
            } else {
                // Jika diatasnya tipe non booking.
                // Proses insert.
                $nomorAntrean = $antreanDiatas->nomor_antrean + 1;
                if($tipe_booking == 0){
                    DB::insert("INSERT INTO jadwal_pasien VALUES(
                        '$id_poli', '$hari', '$id_pasien',
                        '$nomor_antrean', '$tipe_booking', CURRENT_DATE(), CURRENT_TIME(),
                        CURRENT_TIME(), NULL, 1)");
                } else {
                    DB::insert("INSERT INTO jadwal_pasien VALUES(
                        '$id_poli', '$hari', '$id_pasien',
                        '$nomor_antrean', '$tipe_booking', '$tgl_pelayanan', CURRENT_TIME(),
                        '$jam_mulai_dilayani', NULL, 1)");
                }
                return true;
            }
            
        }

    }

    // Kebutuhan Insert Booking
    private function isJadwalTersedia(string $id_poli, string $hari, string $jam_mulai_dilayani){
        $resultCheckRegist = DB::select("SELECT * FROM `poliklinik` 
        JOIN jadwal ON poliklinik.id_poli=jadwal.id_poli 
        WHERE poliklinik.id_poli = '1' AND 
        jadwal.hari = 'SN' AND 
        '$jam_mulai_dilayani' >= CURRENT_TIME() AND
        ('$jam_mulai_dilayani' >= jadwal.jam_buka_booking AND '$jam_mulai_dilayani' <= jadwal.jam_tutup_booking)");
        return ($resultCheckRegist != null);
    }

    public function insertAntrean(Request $request){
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
                if($this->prosesInsert($hari, $id_poli, $id_pasien, $tipe_booking, $jenis_pasien, "", "")){
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], 200);
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
            $jam_mulai_dilayani = $request["jam_mulai_dilayani"];
            if($this->isJadwalTersedia($id_poli, $hari, $jam_mulai_dilayani)){
                // Proses Antrean
                if($this->prosesInsert($hari, $id_poli, $id_pasien, $tipe_booking, $jenis_pasien, $tgl_pelayanan, $jam_mulai_dilayani)){
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil!',
                        'data'      => ''
                    ], 200);
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
