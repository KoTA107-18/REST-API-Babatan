<?php

namespace App\Http\Controllers;

use App\Models\Perawat;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerawatController extends Controller
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

    public function insertPerawat(Request $request){
        $data = [
            'username'  => $request->input('username'),
            'password'  => $request->input('password'),
            'nama'      => $request->input('nama'),
            'id_poli'   => $request->input('id_poli')
        ];

        Perawat::create($data);
    }

    public function editPerawat(Request $request, $id){
        Perawat::where('id_perawat', '=', $id)->update([
            'username'  => $request->input('username'),
            'password'  => $request->input('password'),
            'nama'      => $request->input('nama'),
            'id_poli'   => $request->input('id_poli')
        ]);
    }

    public function deletePerawat($id){
        Perawat::where('id_perawat', '=', $id)->delete();
    }

    public function getAllPerawat(){
        $result = Perawat::with('poliklinik:id_poli,nama_poli')->get();

        if(!$result->isEmpty()){
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getPerawat($id){
        $result = Perawat::with('poliklinik:id_poli,nama_poli')->where('id_perawat', '=', $id)->get();
        if(!$result->isEmpty()){
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function loginPerawat(Request $request){
        $result = Perawat::where([
            ['username', '=', $request->input('username')],
            ['password', '=', $request->input('password')],
        ])->get();
        if(!$result->isEmpty()){
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }
}
