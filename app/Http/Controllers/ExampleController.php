<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        //return response()->json($request);
        return response()->json([
            'response' => '201', 
            'message' => 'Pasien berhasil terdaftar!'
            ]);
    }
}
