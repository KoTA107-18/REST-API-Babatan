<?php

namespace App\Http\Controllers;

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

    // public function administratorLogin(Request $request){
    //     $username = $request["username"];
    //     $password = $request["password"];
    //     $result = DB::select("SELECT * FROM administrator WHERE username = '$username' AND password = '$password'");
    //     if($result != null){
    //         return response()->json(true, 200);
    //     } else {
    //         return response()->json(false, 404);
    //     }
    // }
}
