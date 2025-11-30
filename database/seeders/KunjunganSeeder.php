<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kunjungan;
use App\Models\Pembayaran;
class KunjunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $dataKunjungan = [
        'id_klinik' => 1,
        'id_pasien' => 1,
        'id_dokter' => 1,
        'id_jadwal' => 1,
        'tgl_kunjungan' => '2025-11-30',
        'no_antrian' => 'A-001',
        'keluhan' => 'Sakit perut',
        'status' => 'diperiksa',
        // 'created_at' dan 'updated_at' dihapus agar diisi otomatis oleh Eloquent
    ];

    // Perintah untuk membuat entri Kunjungan
    $kunjungan = Kunjungan::create($dataKunjungan);

    // Setelah Kunjungan dibuat, kita bisa mendapatkan ID-nya untuk Pembayaran
    $idKunjunganBaru = $kunjungan->id; // Asumsi kolom primary key Kunjungan adalah 'id'

    $dataPembayaran = [
        'id_kunjungan' => $idKunjunganBaru, // Menggunakan ID Kunjungan yang baru dibuat
        'id_staff' => 1,
        'total_biaya' => 0,
        'bukti_pembayaran' => 'pembayaran.jpg',
        'metode_bayar' => 'digital',
        'status' => 'pending',
        // 'created_at' dan 'updated_at' dihapus agar diisi otomatis oleh Eloquent
    ];

    // Perintah untuk membuat entri Pembayaran
    Pembayaran::create($dataPembayaran);
    }
}
