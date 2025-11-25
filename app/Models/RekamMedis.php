<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    protected $table = 'rekam_medis';
    protected $guarded = [];

    public function pemeriksaan_mata(){
        return $this->hasOne(PemeriksaanMata::class, 'id_rekam_medis', 'id');
    }

    public function pemeriksaan_gizi(){
        return $this->hasOne(PemeriksaanGizi::class, 'id_rekam_medis', 'id');
    }

    public function resep(){
        return $this->hasMany(Resep::class, 'id_rekam_medis', 'id');
    }

    public function kunjungan(){
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id');
    }
}
