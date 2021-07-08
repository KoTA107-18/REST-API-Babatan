<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Pasien extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pasien';

    public $timestamps = false;

    protected $primaryKey = 'id_pasien';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'no_handphone',
        'api_token',
        'password',
        'kepala_keluarga',
        'tgl_lahir',
        'alamat',
        'nama_lengkap',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'api_token',
        'password',
    ];

    public function jadwalPasien()
    {
        return $this->hasMany(JadwalPasien::class, 'id_pasien', 'id_pasien');
    }

    public function riwayatAntrean()
    {
        return $this->hasMany(RiwayatAntrean::class, 'id_pasien', 'id_pasien');
    }
}
