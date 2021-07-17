<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthPasienController extends Controller
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
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Register Fail!',
                'data'      => ''
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkPasien(Request $request){
        $username           = $request->input('username');
        $no_handphone       = $request->input('no_handphone');
        $resultUsername     = Pasien::where('username', '=', $username)->get();
        $resultHandphone    = Pasien::where('no_handphone', '=', $no_handphone)->get();

        if ( ( $resultUsername != null ) && ( $resultHandphone != null ) ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Username dan Nomor Handphone anda telah digunakan!',
                'data'      => ''
            ], Response::HTTP_CONFLICT);
        } else if ( $resultUsername != null && $resultHandphone == null ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Username anda telah digunakan!',
                'data'      => ''
            ], Response::HTTP_CONFLICT);
        } else if ( $resultUsername == null && $resultHandphone != null ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nomor Handphone anda telah digunakan!',
                'data'      => ''
            ], Response::HTTP_CONFLICT);
        } else {
            return response()->json([
                'success'   => true,
                'message'   => 'Berhasil!',
                'data'      => ''
            ], Response::HTTP_OK);
        }

    }

    public function loginDenganUsername(Request $request)
    {
        $username   = $request->input('username');
        $password   = $request->input('password');

        $pasien     = Pasien::where('username', $username)->first();

        if (Hash::check($password, $pasien->password)) {
            $apiToken = base64_encode(Str::random(40));

            $pasien->update([
                'api_token' => $apiToken
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Login Success!',
                'data'      => [
                    'pasien'    => $pasien,
                    'api_token' => $apiToken
                ]
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Login Fail!',
                'data'      => ''
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function loginDenganNoHp(Request $request)
    {
        $no_handphone   = $request->input('no_handphone');

        $pasien         = Pasien::where('no_handphone', $no_handphone)->first();

        if ($pasien != null) {
            $apiToken = base64_encode(Str::random(40));

            $pasien->update([
                'api_token' => $apiToken
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Login Success!',
                'data'      => [
                    'pasien'    => $pasien,
                    'api_token' => $apiToken
                ]
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Login Fail!',
                'data'      => ''
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
