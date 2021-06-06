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
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli WHERE jadwal_pasien.id_poli='$id' AND jadwal_pasien.status_antrean!=4");
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
        LEFT JOIN poliklinik p ON jadwal_pasien.id_poli=p.id_poli WHERE jadwal_pasien.id_poli='$id' AND jadwal_pasien.status_antrean=4");
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
            DB::delete("DELETE FROM jadwal_pasien WHERE id_poli='$id_poli' AND hari='$hari' AND id_pasien='$id_pasien'");
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
        } else {
            DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean'
                WHERE id_poli = '$id_poli' AND hari='$hari' AND $id_pasien='$id_pasien'");
        }
        
    }

    public function insertAntrean(Request $request){
        $hari = $request["hari"];
        $id_poli = $request["id_poli"];
        $id_pasien = $request["id_pasien"];
        $tipe_booking = $request["tipe_booking"];

        // Jika masih ada antrean yang berjalan.
        $resultCheckRegist = DB::select("SELECT * FROM `jadwal_pasien` WHERE id_pasien = '$id_pasien'");
        if($resultCheckRegist != null){
            return response()->json([
                'success'   => false,
                'message'   => 'Anda sudah mengambil antrean!',
                'data'      => ''
            ], 409);
        } else {
            if($tipe_booking == 1){
                // Belum menambahkan validasi apakah kuota antrean masih ada?
                $tgl_pelayanan = $request["tgl_pelayanan"];
                $jam_mulai_dilayani = $request["jam_mulai_dilayani"];
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    NULL, 1, '$tgl_pelayanan', CURRENT_TIME(),
                    '$jam_mulai_dilayani', NULL, 1)");
            } else {
                DB::insert("INSERT INTO jadwal_pasien VALUES(
                    '$id_poli', '$hari', '$id_pasien',
                    NULL, 0, CURRENT_DATE(), CURRENT_TIME(),
                    NULL, NULL, 1)");
            }
            return response()->json([
                'success'   => true,
                'message'   => 'Berhasil!',
                'data'      => ''
            ], 200);
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
