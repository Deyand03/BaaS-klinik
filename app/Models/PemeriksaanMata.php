<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanMata extends Model
{
    protected $table = 'pemeriksaan_mata';
    protected $guarded = [];

    public function rekam_medis(){
        return $this->belongsTo(RekamMedis::class, 'id_rekam_medis', 'id');
    }
}
