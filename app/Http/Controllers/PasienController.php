<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PasienController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function editPasien(Request $request){
        $id_pasien      = $request->input('id_pasien');
        $no_handphone   = $request->input('no_handphone');
        $username       = $request->input('username');
        $kepala_keluarga= $request->input('kepala_keluarga');
        $tgl_lahir      = $request->input('tgl_lahir');
        $alamat         = $request->input('alamat');
        $nama_lengkap   = $request->input('nama_lengkap');

        Pasien::where('id_pasien', '=', $id_pasien)
            ->update([
                'no_handphone'      => $no_handphone,
                'username'          => $username,
                'kepala_keluarga'   => $kepala_keluarga,
                'tgl_lahir'         => $tgl_lahir,
                'alamat'            => $alamat,
                'nama_lengkap'      => $nama_lengkap,
            ]);

        return response()->json(true, Response::HTTP_OK);
    }

    public function editPasswordPasien(Request $request){
        $id_pasien  = $request->input('id_pasien');
        $password   = Hash::make($request->input('password'));

        Pasien::where('id_pasien', '=', $id_pasien)
            ->update([
                'password'      => $password,
            ]);

        return response()->json(true, Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $apiToken   = explode(' ', $request->header('Authorization'));
        $pasien     = Pasien::where('api_token', $apiToken[1])->first();

        if ($pasien != null) {
            $pasien->update([
                'api_token' => null
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Logout Success!',
                'data'      => [
                    'pasien'    => $pasien
                ]
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Logout Fail!',
                'data'      => ''
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
