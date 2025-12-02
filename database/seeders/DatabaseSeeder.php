<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Pasien; // <--- FIX: Pakai Model Pasien
use App\Models\JadwalPraktek;
use App\Models\Kunjungan;
use App\Models\RekamMedis;
use App\Models\Resep;
use App\Models\Obat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Panggil KlinikSeeder (Pastikan KlinikSeeder sudah direvisi 'info_pembayaran'-nya)
        $this->call([
            KlinikSeeder::class,
        ]);

        echo "Mulai Seeding Data... \n";

        // ============================
        // 2. DOKTER & STAFF
        // ============================
        $userDokter = User::create([
            'email' => 'dokter@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $stafDokter = Staff::create([
            'user_id' => $userDokter->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Dr. Budi Santoso', // FIX: nama_lengkap
            'peran' => 'dokter',
            'spesialisasi' => 'Dokter Umum',
        ]);

        // ============================
        // 3. ADMIN & STAFF
        // ============================
        $userAdmin = User::create([
            'email' => 'admin@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        Staff::create([
            'user_id' => $userAdmin->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Siti Aminah', // FIX: nama_lengkap
            'peran' => 'admin',
        ]);

        // ============================
        // 4. JADWAL PRAKTEK
        // ============================
        $jadwal = JadwalPraktek::create([
            'id_staff' => $stafDokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'kuota_harian' => 10,
            'status_aktif' => true,
        ]);

        // ============================
        // 5. PASIEN
        // ============================
        $userPasien = User::create([
            'email' => 'andi@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);

        // FIX: Pakai Model Pasien & Kolom yang benar
        $pasien = Pasien::create([
            'user_id' => $userPasien->id,
            'nik' => '3276012105980001',
            'nama_lengkap' => 'Andi Pratama',
            'tgl_lahir' => '1998-05-21',
            'no_hp' => '081234567890',
            'golongan_darah' => 'O', // FIX: golongan_darah
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Tidak ada',
            'alamat_domisili' => 'Jl. Melati No. 5' // FIX: alamat_domisili
        ]);

        // ============================
        // 6. OBAT
        // ============================
        $obat1 = Obat::create([
            'id_klinik' => 1,
            'nama_obat' => 'Paracetamol',
            'merk' => 'Sanbe',
            'harga' => 5000,
            'stok' => 100,
            'satuan' => 'tablet',
        ]);

        $obat2 = Obat::create([
            'id_klinik' => 1,
            'nama_obat' => 'Amoxicillin',
            'merk' => 'Kalbe',
            'harga' => 8000,
            'stok' => 80,
            'satuan' => 'botol',
        ]);

        // ============================
        // 7. TRANSAKSI (KUNJUNGAN)
        // ============================
        $kunjungan = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasien->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A001',
            'keluhan' => 'Pusing dan demam',
            'status' => 'menunggu_pembayaran', // FIX: Status sesuai enum baru (karena rekam medis sdh dibuat)
        ]);

        // ============================
        // 8. REKAM MEDIS
        // ============================
        $rekam = RekamMedis::create([
            'id_kunjungan' => $kunjungan->id,
            'diagnosa' => 'Demam Tinggi',
            'anamnesa' => 'Pasien mengeluh pusing sejak dua hari lalu.',
            'tensi_darah' => '120/80 mmHg',
            'berat_badan' => 65,
            'suhu_badan' => 38.5,
            'tindakan' => 'Pemeriksaan fisik',
            'catatan_dokter' => 'Istirahat cukup',
        ]);

        // RESEP
        Resep::create([
            'id_rekam_medis' => $rekam->id,
            'id_obat' => $obat1->id,
            'jumlah' => 10,
            'aturan_pakai' => '3x1 setelah makan',
        ]);

        Resep::create([
            'id_rekam_medis' => $rekam->id,
            'id_obat' => $obat2->id,
            'jumlah' => 1, // Biasanya botol cuma 1
            'aturan_pakai' => '2x1 habiskan',
        ]);

        echo "Seeding Selesai! Data Dummy sudah sinkron dengan Database Baru. \n";
    }
}
