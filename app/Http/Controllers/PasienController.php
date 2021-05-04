<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
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
