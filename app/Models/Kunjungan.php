<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    protected $guarded = [];

    public function rekam_medis(){
        return $this->hasOne(RekamMedis::class, 'id_kunjungan', 'id');
    }

    public function rujukan(){
        return $this->hasOne(Rujukan::class, 'id_kunjungan', 'id');
    }

    public function pembayaran(){
        return $this->hasOne(Pembayaran::class, 'id_kunjungan', 'id');
    }

    public function klinik(){
        return $this->belongsTo(Klinik::class, 'id_klinik', 'id');
    }

    public function pasien(){
        return $this->belongsTo(ProfilPasien::class, 'id_pasien', 'id');
    }

    public function staff(){
        return $this->belongsTo(Staff::class, 'id_dokter', 'id');
    }

    public function jadwal(){
        return $this->belongsTo(JadwalPraktek::class, 'id_jadwal', 'id');
    }
}
