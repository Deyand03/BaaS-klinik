<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasiens'; // Sesuai nama tabel

    protected $fillable = [
        'user_id',
        'nik',
        'nama_lengkap',
        'tgl_lahir',
        'jenis_kelamin',
        'alamat_domisili',
        'no_hp',
        'golongan_darah',
        'riwayat_alergi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class, 'id_pasien', 'id');
    }

    public function rekamMedis()
    {
        return $this->hasManyThrough(RekamMedis::class, Kunjungan::class, 'id_pasien', 'id_kunjungan');
    }
}
