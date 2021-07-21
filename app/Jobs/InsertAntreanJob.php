<?php

namespace App\Jobs;

use App\Models\JadwalPasien;

class InsertAntreanJob extends Job
{
    protected $data;
    protected $id_poli;
    protected $tgl_pelayanan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( array $arrayData )
    {
        $this->data             = $arrayData[0];
        $this->id_poli          = $arrayData[1];
        $this->tgl_pelayanan    = $arrayData[2];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        JadwalPasien::create( $this->data );

        $this->sortNumber( $this->id_poli, $this->tgl_pelayanan );
    }

    private function sortNumber ( int $id_poli, string $tgl_pelayanan )
    {
        $result = JadwalPasien::where([
            ['id_poli', '=', $id_poli],
            ['tgl_pelayanan', '=' , $tgl_pelayanan],
        ])->orderBy('jam_booking', 'asc')->orderBy('waktu_daftar_antrean', 'asc')->get();

        $i = 0;
        while ( $i < count($result) ) {
            $nomor                  = $i + 1;
            $waktu_daftar_antrean   = $result[$i]->waktu_daftar_antrean;
            $idPasien               = $result[$i]->id_pasien;

            JadwalPasien::where('id_poli', '=', $id_poli)
                ->where('id_pasien', '=', $idPasien)
                ->where('waktu_daftar_antrean', '=', $waktu_daftar_antrean)
                ->where('tgl_pelayanan', '=', $tgl_pelayanan)
                ->update(['nomor_antrean' => $nomor]);
            $i++;
        }
    }
}
