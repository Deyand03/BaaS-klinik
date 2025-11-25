<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 'resep';
    protected $guarded = [];
    public function rekam_medis(){
        return $this->belongsTo(RekamMedis::class, 'id_rekam_medis', 'id');
    }

    public function obat(){
        return $this->belongsTo(Obat::class, 'id_obat', 'id');
    }
}
