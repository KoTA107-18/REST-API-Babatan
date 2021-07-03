<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class JadwalPasien extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jadwal_pasien';

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
        'hari',
        'id_pasien',
        'nomor_antrean',
        'tipe_booking',
        'tgl_pelayanan',
        'jam_booking',
        'waktu_daftar_antrean',
        'jam_mulai_dilayani',
        'jam_selesai_dilayani',
        'status_antrean',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien', 'id_pasien');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'id_poli', 'id_poli');
    }

    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'id_poli', 'id_poli');
    }
}
