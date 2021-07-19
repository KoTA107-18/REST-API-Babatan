<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class RiwayatAntrean extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'riwayat_antrean';

    public $timestamps = false;

    protected $primaryKey = 'id_antrean';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_antrean',
        'id_poli',
        'id_pasien',
        'nomor_antrean',
        'tipe_booking',
        'tgl_pelayanan',
        'jam_booking',
        'waktu_daftar_antrean',
        'jam_mulai_dilayani',
        'jam_selesai_dilayani',
        'latitude',
        'longitude',
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

    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'id_poli', 'id_poli');
    }
}
