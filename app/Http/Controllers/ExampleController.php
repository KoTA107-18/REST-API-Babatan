<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function pasienRegister(Request $request){
        $username = $request["username"];
        $no_handphone = $request["no_handphone"];
        $password = $request["password"];
        $kepala_keluarga = $request["kepala_keluarga"];
        $tgl_lahir = $request["tgl_lahir"];
        $alamat = $request["alamat"];
        $nama_lengkap = $request["nama_lengkap"];
        
        DB::insert("INSERT INTO pasien VALUES(
            '$username', '$no_handphone', '$password', 
            '$kepala_keluarga', '$tgl_lahir', '$alamat', 
            '$nama_lengkap')");
    }

    public function getAdmin(){
        $results = DB::select("SELECT * FROM administrator");
        return response()->json($results);
    }
}
