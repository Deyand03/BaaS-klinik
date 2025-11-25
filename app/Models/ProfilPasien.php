<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPasien extends Model
{
    protected $table = 'profil_pasien';
    protected $guarded = [];

    public function kunjungan(){
        return $this->hasMany(Kunjungan::class, 'id_pasien', 'id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
