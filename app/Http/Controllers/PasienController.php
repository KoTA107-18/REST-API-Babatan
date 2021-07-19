<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
        $latitude       = $request->input('latitude');
        $longitude      = $request->input('longitude');
        $nama_lengkap   = $request->input('nama_lengkap');

        $register       = Pasien::create([
            'username'        => $username,
            'no_handphone'    => $no_handphone,
            'password'        => $password,
            'kepala_keluarga' => $kepala_keluarga,
            'tgl_lahir'       => $tgl_lahir,
            'alamat'          => $alamat,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
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

    public function getPasien(Request $request, $id){
        $result = Pasien::where('id_pasien', '=', $id)->first();
        if ( !$result->isEmpty() ) {
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function editPasien(Request $request){
        $id_pasien      = $request->input('id_pasien');
        $no_handphone   = $request->input('no_handphone');
        $username       = $request->input('username');
        $kepala_keluarga= $request->input('kepala_keluarga');
        $tgl_lahir      = $request->input('tgl_lahir');
        $alamat         = $request->input('alamat');
        $latitude       = $request->input('latitude');
        $longitude      = $request->input('longitude');
        $nama_lengkap   = $request->input('nama_lengkap');

        Pasien::where('id_pasien', '=', $id_pasien)
            ->update([
                'no_handphone'      => $no_handphone,
                'username'          => $username,
                'kepala_keluarga'   => $kepala_keluarga,
                'tgl_lahir'         => $tgl_lahir,
                'alamat'            => $alamat,
                'latitude'        => $latitude,
                'longitude'       => $longitude,
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
