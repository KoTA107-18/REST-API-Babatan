<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdministratorController extends Controller
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

    // Administrator
    public function administratorLogin(Request $request){
        $username   = $request->input('username');
        $password   = $request->input('password');
        $result     = Administrator::where('username', '=', $username)
                                   ->where('password', '=', $password)
                                   ->get();

        if( !$result->isEmpty() ){
            return response()->json(true, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }
}
