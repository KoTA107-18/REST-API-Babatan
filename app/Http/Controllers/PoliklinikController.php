<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Poliklinik;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PoliklinikController extends Controller
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

    public function getAllPoliklinik()
    {
        $result     = [];
        $resultPoli = Poliklinik::all();

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i]         = $row;
            $idPoli             = $row->id_poli;
            $resultJadwal       = Jadwal::where('id_poli', '=', $idPoli)->get();
            $result[$i]->jadwal = $resultJadwal;
            $i++;
        }

        if( $result != null ){
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function getPoliklinik($id)
    {
        $result     = [];
        $resultPoli = Poliklinik::where('id_poli', '=', $id)->get();

        $i = 0;
        foreach ($resultPoli as $row) {
            $result[$i]         = $row;
            $idPoli             = $row->id_poli;
            $resultJadwal       = Jadwal::where('id_poli', '=', $idPoli)->get();
            $result[$i]->jadwal = $resultJadwal;
            $i++;
        }

        if( $result != null ){
            return response()->json($result, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function insertPoliklinik(Request $request)
    {
        $nama_poli      = $request->input('nama_poli');
        $desc_poli      = $request->input('desc_poli');
        $status_poli    = $request->input('status_poli');
        $rerata         = $request->input('rerata_waktu_pelayanan');
        $jadwal         = $request->input('jadwal');

        Poliklinik::create([
            'nama_poli'                 => $nama_poli,
            'desc_poli'                 => $desc_poli,
            'status_poli'               => $status_poli,
            'rerata_waktu_pelayanan'    => $rerata,
        ]);

        foreach ($jadwal as $jadwalPerHari) {
            $hari               = $jadwalPerHari["hari"];
            $jam_buka_booking   = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking  = $jadwalPerHari["jam_tutup_booking"];
            $id_poli            = Poliklinik::select('id_poli')->where('nama_poli', '=', $nama_poli)->get();

            $data = [
                'hari'              => $hari,
                'jam_buka_booking'  => $jam_buka_booking,
                'jam_tutup_booking' => $jam_tutup_booking,
                'id_poli'           => $id_poli,
            ];

            Jadwal::create($data);
        }
    }

    public function ubahPoliklinik(Request $request, $id)
    {
        $id_poli        = $id;
        $nama_poli      = $request->input('nama_poli');
        $desc_poli      = $request->input('desc_poli');
        $status_poli    = $request->input('status_poli');
        $rerata         = $request->input('rerata_waktu_pelayanan');
        $jadwal         = $request->input('jadwal');

        Poliklinik::where('id_poli', '=', $id_poli)
            ->update([
                'nama_poli'                 => $nama_poli,
                'desc_poli'                 => $desc_poli,
                'status_poli'               => $status_poli,
                'rerata_waktu_pelayanan'    => $rerata,
            ]);

        Jadwal::where('id_poli', '=', $id_poli)->delete();

        foreach ($jadwal as $jadwalPerHari) {
            $hari               = $jadwalPerHari["hari"];
            $jam_buka_booking   = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking  = $jadwalPerHari["jam_tutup_booking"];

            $data = [
                'hari'              => $hari,
                'jam_buka_booking'  => $jam_buka_booking,
                'jam_tutup_booking' => $jam_tutup_booking,
                'id_poli'           => $id_poli,
            ];

            Jadwal::create($data);
        }
    }

    public function ubahStatusAllPoli(Request $request)
    {
        $i = 0;
        while ( $request[$i] != null ) {
            $id     = $request[$i]["id_poli"];
            $status = $request[$i]["status_poli"];
            Poliklinik::where('id_poli', '=', $id)->update(['status_poli' => $status]);
            $i++;
        }

        if($i != 0){
            return response()->json($i, Response::HTTP_OK);
        } else {
            return response()->json(false, Response::HTTP_NOT_FOUND);
        }
    }

    public function deletePoliklinik($id){
        Jadwal::where('id_poli', '=', $id)->delete();
        Poliklinik::where('id_poli', '=', $id)->delete();
    }
}
