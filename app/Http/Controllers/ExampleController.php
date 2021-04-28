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
        //
    }

    public function administratorLogin(Request $request){
        $username = $request["username"];
        $password = $request["password"];
        $result = DB::select("SELECT * FROM administrator WHERE username = '$username' AND password = '$password'");
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

    public function registerAntreanHariIni(Request $request){
        $username = $request["username"];
        $id_jadwal = $request["id_jadwal"];
        $id_poli = $request["id_poli"];
        $kode_antrean = $request["kode_antrean"];
        $tipe_booking = $request["tipe_booking"];
        $tgl_pelayanan = $request["tgl_pelayanan"];
        $jam_mulai_dilayani = $request["jam_mulai_dilayani"];
        $jam_selesai_dilayani = $request["jam_selesai_dilayani"];
        $status_antrean = $request["status_antrean"];

        DB::insert("INSERT INTO jadwal_pasien VALUES(
            '$username', $id_jadwal, '$id_poli', 
            '0', $tipe_booking, '$tgl_pelayanan', 
            $jam_mulai_dilayani,$jam_selesai_dilayani,'$status_antrean')");
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
}
