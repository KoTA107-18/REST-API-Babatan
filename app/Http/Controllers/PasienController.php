<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pasien;

class PasienController extends Controller
{
    public function register(Request $request)
    {
        $username       = $request->input('username');
        $no_handphone   = $request->input('no_handphone');
        $password       = Hash::make($request->input('password'));
        $kepala_keluarga= $request->input('kepala_keluarga');
        $tgl_lahir      = $request->input('tgl_lahir');
        $alamat         = $request->input('alamat');
        $nama_lengkap   = $request->input('nama_lengkap');

        $register       = Pasien::create([
            'username'        => $username,
            'no_handphone'    => $no_handphone,
            'password'        => $password,
            'kepala_keluarga' => $kepala_keluarga,
            'tgl_lahir'       => $tgl_lahir,
            'alamat'          => $alamat,
            'nama_lengkap'    => $nama_lengkap
        ]);

        if ($register) {
            return response()->json([
                'success'   => true,
                'message'   => 'Register Success!',
                'data'      => $register
            ], 201);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Register Fail!',
                'data'      => ''
            ], 400);
        }
    }

    public function login(Request $request)
    {

    }

    // public function pasienRegister(Request $request){
    //     $username = $request["username"];
    //     $no_handphone = $request["no_handphone"];
    //     $password = $request["password"];
    //     $kepala_keluarga = $request["kepala_keluarga"];
    //     $tgl_lahir = $request["tgl_lahir"];
    //     $alamat = $request["alamat"];
    //     $nama_lengkap = $request["nama_lengkap"];

    //     DB::insert("INSERT INTO pasien VALUES(
    //         '$username', '$no_handphone', '$password',
    //         '$kepala_keluarga', '$tgl_lahir', '$alamat',
    //         '$nama_lengkap')");
    // }

    // public function getAdmin(){
    //     $results = DB::select("SELECT * FROM administrator");
    //     return response()->json($results);
    // }
}
