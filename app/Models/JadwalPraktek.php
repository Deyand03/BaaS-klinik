<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPraktek extends Model
{
    protected $table = 'jadwal_praktek';
    protected $guarded = [];

    public function kunjungan(){
        return $this->hasMany(Kunjungan::class, 'id_jadwal', 'id');
    }

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staff', 'id');
    }
}
