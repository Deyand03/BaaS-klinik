<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $table = 'obat';
    protected $guarded = [];

    public function resep(){
        return $this->hasMany(Resep::class, 'id_obat', 'id');
    }

    public function klinik(){
        return $this->belongsTo(Klinik::class, 'id_klinik', 'id');
    }
}
