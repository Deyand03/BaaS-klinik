<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Klinik;
use App\Models\Staff;
use App\Models\JadwalPraktek;
use App\Models\ProfilPasien;
use App\Models\Kunjungan;
use App\Models\RekamMedis;
use App\Models\PemeriksaanGizi;
use App\Models\PemeriksaanMata;
use App\Models\Resep;
use App\Models\Obat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Panggil KlinikSeeder dahulu
        $this->call([
            KlinikSeeder::class,
        ]);

        // ============================
        //  USER & STAFF EXISTING CODE
        // ============================

        // 3. User Dokter
        $userDokter = User::create([
            'email' => 'dokter@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 4. Staff Dokter
        $stafDokter = Staff::create([
            'user_id' => $userDokter->id,
            'id_klinik' => 1,
            'nama' => 'Dr. Budi Santoso',
            'peran' => 'dokter',
            'spesialisasi' => 'Dokter Umum',
        ]);

        // 5. User Admin
        $userAdmin = User::create([
            'email' => 'admin@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 6. Staff Admin
        Staff::create([
            'user_id' => $userAdmin->id,
            'id_klinik' => 1,
            'nama' => 'Siti Aminah',
            'peran' => 'admin',
            'spesialisasi' => null,
        ]);

        // 7. Jadwal Praktek Dokter Budi
        $jadwal = JadwalPraktek::create([
            'id_staff' => $stafDokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'kuota_harian' => 10,
            'status_aktif' => true,
        ]);

        // Pasien User
        $userPasien = User::create([
            'email' => 'andi@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);

        // Profil Pasien
        $profil = ProfilPasien::create([
            'user_id' => $userPasien->id,
            'nik' => '3276012105980001',
            'nama_lengkap' => 'Andi Pratama',
            'tgl_lahir' => '1998-05-21',
            'no_hp' => '081234567890',
            'gol_darah' => 'O',
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Tidak ada',
            'alamat' => 'Jl. Melati No. 5'
        ]);

        // Obat
        $obat1 = Obat::create([
            'id_klinik' => '1',
            'nama_obat' => 'Paracetamol',
            'merk' => 'Sanbe',
            'harga' => 5000,
            'stok' => 100,
            'satuan' => 'tablet',
        ]);

        $obat2 = Obat::create([
            'nama_obat' => 'Amoxicillin',
            'stok' => 80,
            'merk' => 'Kalbe',
            'harga' => 8000,
            'id_klinik' => '1',
            'satuan' => 'botol',
        ]);

        // Kunjungan
        $kunjungan = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $profil->id, 
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A001',
            'keluhan' => 'Pusing dan demam',
            'status' => 'diperiksa',
        ]);

        // Rekam Medis
        $rekam = RekamMedis::create([
            'id_kunjungan' => $kunjungan->id,
            'diagnosa' => 'Demam Tinggi',
            'anamnesa' => 'Pasien mengeluh pusing sejak dua hari yang lalu disertai demam tinggi.',
            'tensi_darah' => '120/80 mmHg',
            'berat_badan' => 65,
            'suhu_badan' => 38.5,
            'tindakan' => 'Pemeriksaan fisik & pemberian obat',
            'catatan_dokter' => 'Istirahat cukup dan minum obat secara teratur',
        ]);

        // Pemeriksaan Gizi
        PemeriksaanGizi::create([
            'id_rekam_medis' => $rekam->id,
            'tinggi_badan' => 170,
            // 'berat_badan' => 65,
            'imt' => 22.5,
            'status_gizi' => 'Normal',
            'lingkar_perut' => 80,

        ]);

        // Pemeriksaan Mata
        PemeriksaanMata::create([
            'id_rekam_medis' => $rekam->id,
            'visus_od' => '6/6',
            'visus_os' => '6/6',
            'sphere_od' => '-0.50',
            'cylinder_od' => '-0.25', 
            'axis_od' => '90',
            'sphere_os' => '-0.75',
            'cylinder_os' => '-0.50',
            'axis_os' => '85',
            'pd' => 62,
        ]);

        // Resep Obat
        Resep::create([
            'id_rekam_medis' => $rekam->id,
            'id_obat' => $obat1->id,
            'jumlah' => 10,
            'aturan_pakai' => '3x1 setelah makan',
        ]);

        Resep::create([
            'id_rekam_medis' => $rekam->id,
            'id_obat' => $obat2->id,
            'jumlah' => 12,
            'aturan_pakai' => '2x1 sebelum makan',
        ]);

        echo "Data Dummy Lengkap Telah Dibuat! \n";
    }
}
