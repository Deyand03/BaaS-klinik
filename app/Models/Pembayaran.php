<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $guarded = [];

    public function kunjungan(){
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id');
    }
    public function staff(){
        return $this->belongsTo(Staff::class, 'id_staff', 'id');
    }

}
