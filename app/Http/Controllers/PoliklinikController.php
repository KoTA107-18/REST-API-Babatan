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
        $batas_booking  = $request->input('batas_booking');

        $poli       = new Poliklinik;
        $poli->fill([
            'nama_poli'                 => $nama_poli,
            'desc_poli'                 => $desc_poli,
            'status_poli'               => $status_poli,
            'rerata_waktu_pelayanan'    => $rerata,
            'batas_booking'             => $batas_booking,
        ]);
        $poli->save();
        $id_poli    = $poli->id_poli;

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

    public function ubahPoliklinik(Request $request, $id)
    {
        $id_poli        = $id;

        try {
            Jadwal::where('id_poli', '=', $id_poli)->delete();
        } catch(\Exception $e) {
            return response()->json([
                'success'   => false,
                'message'   => 'Jadwal sedang digunakan! Tunggu hingga antrean kosong!',
                'data'      => ''
            ], $e->getCode());
        }

        $nama_poli      = $request->input('nama_poli');
        $desc_poli      = $request->input('desc_poli');
        $status_poli    = $request->input('status_poli');
        $rerata         = $request->input('rerata_waktu_pelayanan');
        $jadwal         = $request->input('jadwal');
        $batas_booking  = $request->input('batas_booking');

        Poliklinik::where('id_poli', '=', $id_poli)
            ->update([
                'nama_poli'                 => $nama_poli,
                'desc_poli'                 => $desc_poli,
                'status_poli'               => $status_poli,
                'rerata_waktu_pelayanan'    => $rerata,
                'batas_booking'             => $batas_booking,
            ]);

        foreach ($jadwal as $jadwalPerHari) {
            $hari               = $jadwalPerHari["hari"];
            $jam_buka_booking   = $jadwalPerHari["jam_buka_booking"];
            $jam_tutup_booking  = $jadwalPerHari["jam_tutup_booking"];

            $data = [
                'id_poli'           => $id_poli,
                'hari'              => $hari,
                'jam_buka_booking'  => $jam_buka_booking,
                'jam_tutup_booking' => $jam_tutup_booking,
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

    // Buka Tutup Portal
    function bukaPortal(){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DAY    = strtoupper(date("D", strtotime("now")));
        $kodeHari       = "";

        if($CURRENT_DAY == "SUN"){
            $kodeHari = "MG";
        } else if($CURRENT_DAY == "MON"){
            $kodeHari = "SN";
        } else if($CURRENT_DAY == "TUE"){
            $kodeHari = "SL";
        } else if($CURRENT_DAY == "WED"){
            $kodeHari = "RB";
        } else if($CURRENT_DAY == "THU"){
            $kodeHari = "KM";
        } else if($CURRENT_DAY == "FRI"){
            $kodeHari = "JM";
        } else if($CURRENT_DAY == "SAT"){
            $kodeHari = "SB";
        }

        $resultPoli = Jadwal::with('poliklinik')->where('hari', '=', $kodeHari)->get();

        if ( !$resultPoli->isEmpty() ) {
            foreach ($resultPoli as $row) {
                $idPoli = $row->poliklinik->id_poli;
                $resultJadwal = Poliklinik::where('id_poli', '=', $idPoli)->update(['status_poli' => 1]);
            }
        }
    }

    function tutupPortal(){
        date_default_timezone_set("Asia/Jakarta");
        $CURRENT_DAY    = strtoupper(date("D", strtotime("now")));
        $kodeHari       = "";

        if($CURRENT_DAY == "SUN"){
            $kodeHari = "MG";
        } else if($CURRENT_DAY == "MON"){
            $kodeHari = "SN";
        } else if($CURRENT_DAY == "TUE"){
            $kodeHari = "SL";
        } else if($CURRENT_DAY == "WED"){
            $kodeHari = "RB";
        } else if($CURRENT_DAY == "THU"){
            $kodeHari = "KM";
        } else if($CURRENT_DAY == "FRI"){
            $kodeHari = "JM";
        } else if($CURRENT_DAY == "SAT"){
            $kodeHari = "SB";
        }

        $resultPoli = Jadwal::with('poliklinik')->where('hari', '=', $kodeHari)->get();

        if ( !$resultPoli->isEmpty() ) {
            foreach ($resultPoli as $row) {
                $idPoli = $row->poliklinik->id_poli;
                $resultJadwal = Poliklinik::where('id_poli', '=', $idPoli)->update(['status_poli' => 0]);
            }
        }
    }
}
