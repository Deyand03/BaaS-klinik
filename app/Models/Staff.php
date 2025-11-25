<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $guarded = [];

    public function kunjungan(){
        return $this->hasMany(Kunjungan::class, 'id_staff', 'id');
    }
    public function jadwal(){
        return $this->hasMany(JadwalPraktek::class, 'id_staff', 'id');
    }
    public function pembayaran(){
        return $this->hasMany(Pembayaran::class, 'id_staff', 'id');
    }
    public function klinik(){
        return $this->belongsTo(Klinik::class, 'id_klinik', 'id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
