<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Lumen\Auth\Authorizable;

class Poliklinik extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'poliklinik';

    public $timestamps = false;

    protected $primaryKey = 'id_poli';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_poli',
        'nama_poli',
        'desc_poli',
        'status_poli',
        'rerata_waktu_pelayanan',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    // Menghasilkan data jadwal per poli
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_poli', 'id_poli');
    }

    // Menghasilkan data perawat per poli
    public function perawat()
    {
        return $this->hasMany(Perawat::class, 'id_poli', 'id_poli');
    }

    // Menghasilkan data jadwal pasien per poli
    public function jadwalPasien()
    {
        return $this->hasManyThrough(JadwalPasien::class, Jadwal::class, 'id_poli', 'id_poli', 'id_poli', 'id_poli')->distinct();
    }

    // Menghasilkan total antrean per poli
    public function totalAntrean()
    {
        return $this->jadwalPasien()->selectRaw('count(distinct jadwal_pasien.id_pasien) as result')->groupBy('jadwal_pasien.id_poli')->where('jadwal_pasien.tgl_pelayanan', '=', Carbon::today());
    }

    // Menghasilkan total antrean sementara
    public function antreanSementara()
    {
        return $this->jadwalPasien()->selectRaw('count(distinct jadwal_pasien.id_pasien) as result')->groupBy('jadwal_pasien.id_poli')->where([['jadwal_pasien.tgl_pelayanan', '=', Carbon::today()], ['jadwal_pasien.status_antrean', '=', 4]]);
    }

    // Menghasilkan
    public function nomorAntrean()
    {
        return $this->jadwalPasien()->selectRaw('max(distinct jadwal_pasien.nomor_antrean) as result')->groupBy('jadwal_pasien.id_poli')->where([['jadwal_pasien.tgl_pelayanan', '=', Carbon::today()], ['jadwal_pasien.status_antrean', '=', 2]]);
    }
}
