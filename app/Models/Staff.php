<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $guarded = [];

    // Di file Staff.php

    public function kunjungan()
    {
        // FIX: Ganti 'id_staff' jadi 'id_dokter' (sesuai kolom di tabel kunjungan)
        return $this->hasMany(Kunjungan::class, 'id_dokter', 'id');
    }
    public function jadwal()
    {
        return $this->hasMany(JadwalPraktek::class, 'id_staff', 'id');
    }
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_staff', 'id');
    }
    public function klinik()
    {
        return $this->belongsTo(Klinik::class, 'id_klinik', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
