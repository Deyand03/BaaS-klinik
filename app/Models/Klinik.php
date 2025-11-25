<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klinik extends Model
{
    protected $table = 'klinik';
    protected $guarded = [];

    public function staff(){
        return $this->hasMany(Staff::class, 'id_klinik', 'id');
    }

    public function obat(){
        return $this->hasMany(Obat::class, 'id_klinik', 'id');
    }

    public function kunjungan(){
        return $this->hasMany(Kunjungan::class, 'id_klinik', 'id');
    }

}
