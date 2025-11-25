<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanGizi extends Model
{
    protected $table = 'pemeriksaan_gizi';
    protected $guarded = [];

    public function rekam_medis(){
        return $this->belongsTo(RekamMedis::class, 'id_rekam_medis', 'id');
    }

}
