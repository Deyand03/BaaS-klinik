<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Pasien;
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
        // 1. Panggil KlinikSeeder DULUAN
        $this->call([
            KlinikSeeder::class,
        ]);

        echo "Mulai Seeding Data Lengkap... \n";

        // ============================
        // A. BUAT AKUN STAFF (LENGKAP)
        // ============================

        // 1. ADMIN
        $userAdmin = User::create([
            'email' => 'admin@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        Staff::create([
            'user_id' => $userAdmin->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Siti Aminah',
            'peran' => 'admin',
        ]);

        // 2. DOKTER UMUM
        $userDokter = User::create([
            'email' => 'dokter@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        $stafDokter = Staff::create([
            'user_id' => $userDokter->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'dr. Budi Santoso',
            'peran' => 'dokter',
            'spesialisasi' => 'Dokter Umum',
        ]);

        // 3. RESEPSIONIS
        $userResepsionis = User::create([
            'email' => 'resepsionis@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        Staff::create([
            'user_id' => $userResepsionis->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Rina Frontdesk',
            'peran' => 'resepsionis',
        ]);

        // 4. PERAWAT
        $userPerawat = User::create([
            'email' => 'perawat@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        Staff::create([
            'user_id' => $userPerawat->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Susanti S.Kep',
            'peran' => 'perawat',
        ]);

        // 5. KASIR (YANG BARU DITAMBAH)
        $userKasir = User::create([
            'email' => 'kasir@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        Staff::create([
            'user_id' => $userKasir->id,
            'id_klinik' => 1,
            'nama_lengkap' => 'Budi Kasir',
            'peran' => 'kasir',
        ]);


        // ============================
        // B. JADWAL PRAKTEK
        // ============================
        $jadwal = JadwalPraktek::create([
            'id_staff' => $stafDokter->id,
            'hari' => 'Senin', // Pastikan test hari ini sesuai atau ubah manual nanti
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'kuota_harian' => 20,
            'status_aktif' => true,
        ]);


        // ============================
        // C. DATA PASIEN (DUMMY)
        // ============================

        // Pasien 1: Andi (Sudah selesai diperiksa, siap bayar)
        $userAndi = User::create([
            'email' => 'andi@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienAndi = Pasien::create([
            'user_id' => $userAndi->id,
            'nik' => '3276012105980001',
            'nama_lengkap' => 'Andi Pratama',
            'tgl_lahir' => '1998-05-21',
            'no_hp' => '081234567890',
            'golongan_darah' => 'O',
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Tidak ada',
            'alamat_domisili' => 'Jl. Melati No. 5'
        ]);

        // Pasien 2: Siti (Baru Booking)
        $userSiti = User::create([
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienSiti = Pasien::create([
            'user_id' => $userSiti->id,
            'nik' => '3276012105980002',
            'nama_lengkap' => 'Siti Maimunah',
            'tgl_lahir' => '2000-01-12',
            'no_hp' => '081234567891',
            'golongan_darah' => 'A',
            'jenis_kelamin' => 'Perempuan',
            'riwayat_alergi' => 'Seafood',
            'alamat_domisili' => 'Jl. Anggrek No. 12'
        ]);


        // ============================
        // D. DATA MASTER OBAT
        // ============================
        $obat1 = Obat::create([
            'id_klinik' => 1,
            'nama_obat' => 'Paracetamol 500mg',
            'merk' => 'Sanbe',
            'harga' => 5000,
            'stok' => 100,
            'satuan' => 'tablet',
        ]);

        $obat2 = Obat::create([
            'id_klinik' => 1,
            'nama_obat' => 'Amoxicillin 500mg',
            'merk' => 'Kalbe',
            'harga' => 8000,
            'stok' => 80,
            'satuan' => 'botol',
        ]);


        // ============================
        // E. TRANSAKSI (SIMULASI ALUR)
        // ============================

        // KASUS 1: Andi (Status: Menunggu Pembayaran / Kasir)
        $kunjunganAndi = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienAndi->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(), // Hari Ini
            'no_antrian' => 'A-001',
            'keluhan' => 'Demam tinggi sejak semalam',
            'status' => 'menunggu_pembayaran',
        ]);

        // Rekam Medis Andi
        $rekamAndi = RekamMedis::create([
            'id_kunjungan' => $kunjunganAndi->id,
            'diagnosa' => 'Febris (Demam)',
            'anamnesa' => 'Pasien mengeluh pusing, badan panas dingin.',
            'tensi_darah' => '120/80 mmHg',
            'berat_badan' => 65,
            'suhu_badan' => 38.5,
            'tindakan' => 'Pemeriksaan fisik & resep obat',
            'catatan_dokter' => 'Istirahat cukup 3 hari',
        ]);

        // Resep Andi
        Resep::create(['id_rekam_medis' => $rekamAndi->id, 'id_obat' => $obat1->id, 'jumlah' => 10, 'aturan_pakai' => '3x1 sesudah makan']);
        Resep::create(['id_rekam_medis' => $rekamAndi->id, 'id_obat' => $obat2->id, 'jumlah' => 1, 'aturan_pakai' => '2x1 habiskan']);


        // KASUS 2: Siti (Status: Booking / Resepsionis)
        Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienSiti->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(), // Hari Ini
            'no_antrian' => 'A-002',
            'keluhan' => 'Sakit gigi geraham bawah',
            'status' => 'booking', // Masih di Resepsionis
        ]);

        echo "Seeding Selesai! \n";
    }
}
