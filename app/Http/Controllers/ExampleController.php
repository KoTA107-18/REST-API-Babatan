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

    public function getPoliklinik(){
        $result = DB::select("SELECT * FROM poliklinik");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function getAntreanWithPoliId(Request $request){
        $id_poli = $request["id_poli"];
        $result = DB::select("SELECT * FROM jadwal_pasien WHERE id_poli='$id_poli'");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json(false, 404);
        }
    }

    public function insertPoliklinik(Request $request){
        $id_poli = $request["id_poli"];
        $nama_poli = $request["nama_poli"];
        $desc_poli = $request["desc_poli"];
        $status_poli = $request["status_poli"];
        $rerata = $request["rerata_waktu_pelayanan"];
        DB::insert("INSERT poliklinik VALUES(0,'$nama_poli', '$desc_poli', $status_poli, $rerata)");
    }

    public function ubahPoliklinik(Request $request){
        $id_poli = $request["id_poli"];
        $nama_poli = $request["nama_poli"];
        $desc_poli = $request["desc_poli"];
        $status_poli = $request["status_poli"];
        $rerata = $request["rerata_waktu_pelayanan"];

        DB::update("UPDATE poliklinik SET nama_poli = '$nama_poli',
        desc_poli = '$desc_poli', status_poli = '$status_poli',
        rerata_waktu_pelayanan = '$rerata' WHERE id_poli = '$id_poli'");
    }

    public function registerAntreanHariIni(Request $request){
        $id_jadwal_pasien = $request["id_jadwal_pasien"];
        $id_poli = $request["id_poli"];
        $id_hari = $request["id_hari"];
        $username = $request["username"];
        $nomor_antrean = $request["nomor_antrean"];
        $tipe_booking = $request["tipe_booking"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $jam_daftar_antrean = $request["jam_daftar_antrean"];
        $jam_mulai_dilayani = $request["jam_mulai_dilayani"];
        $jam_selesai_dilayani = $request["jam_selesai_dilayani"];
        $status_antrean = $request["status_antrean"];

        if($tipe_booking == 1){
            DB::insert("INSERT INTO jadwal_pasien VALUES(
                '$id_jadwal_pasien','$id_poli', '$id_hari', '$username',
                '0', '$tipe_booking', '$tgl_pelayanan', '$jam_daftar_antrean',
                '$jam_mulai_dilayani',$jam_selesai_dilayani,'$status_antrean')");
        } else {
            DB::insert("INSERT INTO jadwal_pasien VALUES(
                '$id_jadwal_pasien','$id_poli', '$id_hari', '$username',
                '0', '$tipe_booking', '$tgl_pelayanan', '$jam_daftar_antrean',
                $jam_mulai_dilayani,$jam_selesai_dilayani,'$status_antrean')");
        }

        
    }

    public function checkStatusTicket(Request $request){
        $username = $request["username"];
        $result = DB::select("SELECT * FROM `jadwal_pasien` WHERE username = '$username' AND status_antrean = 1");
        if($result != null){
            return response()->json($result, 200);
        } else {
            return response()->json($result, 404);
        }
    }

    public function ubahAntrean(Request $request){
        $id_poli = $request["id_poli"];
        $id_hari = $request["id_hari"];
        $username = $request["username"];
        $nomor_antrean = $request["nomor_antrean"];
        $tipe_booking = $request["tipe_booking"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $jam_daftar_antrean = $request["jam_daftar_antrean"];
        $jam_mulai_dilayani = $request["jam_mulai_dilayani"];
        $jam_selesai_dilayani = $request["jam_selesai_dilayani"];
        $status_antrean = $request["status_antrean"];

        DB::update("UPDATE jadwal_pasien SET status_antrean = '$status_antrean' WHERE username = '$username' AND status_antrean ='1'");
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
