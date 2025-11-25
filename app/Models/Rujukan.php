<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rujukan extends Model
{
    protected $table = 'rujukan';
    protected $guarded = [];

    public function kunjungan(){
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id');
    }
}
