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

        // Pasien 3
        $userBudi = User::create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienBudi = Pasien::create([
            'user_id' => $userBudi->id,
            'nik' => '3276012105980003',
            'nama_lengkap' => 'Budi Hartono',
            'tgl_lahir' => '1992-03-15',
            'no_hp' => '081234567892',
            'golongan_darah' => 'B',
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Obat penicillin',
            'alamat_domisili' => 'Jl. Kenanga No. 7'
        ]);

        // Pasien 4
        $userMaya = User::create([
            'email' => 'maya@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienMaya = Pasien::create([
            'user_id' => $userMaya->id,
            'nik' => '3276012105980004',
            'nama_lengkap' => 'Maya Lestari',
            'tgl_lahir' => '1985-11-02',
            'no_hp' => '081234567893',
            'golongan_darah' => 'AB',
            'jenis_kelamin' => 'Perempuan',
            'riwayat_alergi' => 'Serbuk bunga',
            'alamat_domisili' => 'Jl. Flamboyan No. 21'
        ]);

        // Pasien 5
        $userRizky = User::create([
            'email' => 'rizky@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienRizky = Pasien::create([
            'user_id' => $userRizky->id,
            'nik' => '3276012105980005',
            'nama_lengkap' => 'Rizky Hadi',
            'tgl_lahir' => '1978-07-30',
            'no_hp' => '081234567894',
            'golongan_darah' => 'O',
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Tidak ada',
            'alamat_domisili' => 'Jl. Dahlia No. 3'
        ]);

        // Pasien 6
        $userLina = User::create([
            'email' => 'lina@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienLina = Pasien::create([
            'user_id' => $userLina->id,
            'nik' => '3276012105980006',
            'nama_lengkap' => 'Lina Wardani',
            'tgl_lahir' => '1995-09-10',
            'no_hp' => '081234567895',
            'golongan_darah' => 'A',
            'jenis_kelamin' => 'Perempuan',
            'riwayat_alergi' => 'Latex',
            'alamat_domisili' => 'Jl. Sawo No. 10'
        ]);

        // Pasien 7
        $userAgus = User::create([
            'email' => 'agus@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienAgus = Pasien::create([
            'user_id' => $userAgus->id,
            'nik' => '3276012105980007',
            'nama_lengkap' => 'Agus Santoso',
            'tgl_lahir' => '1988-12-05',
            'no_hp' => '081234567896',
            'golongan_darah' => 'B',
            'jenis_kelamin' => 'Laki-laki',
            'riwayat_alergi' => 'Seafood',
            'alamat_domisili' => 'Jl. Bougenville No. 2'
        ]);

        // Pasien 8
        $userTari = User::create([
            'email' => 'tari@example.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
        ]);
        $pasienTari = Pasien::create([
            'user_id' => $userTari->id,
            'nik' => '3276012105980008',
            'nama_lengkap' => 'Tari Wulandari',
            'tgl_lahir' => '2002-04-18',
            'no_hp' => '081234567897',
            'golongan_darah' => 'O',
            'jenis_kelamin' => 'Perempuan',
            'riwayat_alergi' => 'Tidak ada',
            'alamat_domisili' => 'Jl. Teratai No. 14'
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

        // // Rekam Medis Andi
        // $rekamAndi = RekamMedis::create([
        //     'id_kunjungan' => $kunjunganAndi->id,
        //     'diagnosa' => 'Febris (Demam)',
        //     'anamnesa' => 'Pasien mengeluh pusing, badan panas dingin.',
        //     'tensi_darah' => '120/80 mmHg',
        //     'berat_badan' => 65,
        //     'suhu_badan' => 38.5,
        //     'tindakan' => 'Pemeriksaan fisik & resep obat',
        //     'catatan_dokter' => 'Istirahat cukup 3 hari',
        // ]);

        // KASUS 1 & 2: Di Resepsionis
        $kunjunganAndi = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienAndi->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-001',
            'keluhan' => 'Demam tinggi sejak semalam',
            'status' => 'booking',
        ]);

        $kunjunganSiti = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienSiti->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-002',
            'keluhan' => 'Sakit gigi geraham bawah',
            'status' => 'booking',
        ]);

        // KASUS 3 & 4: Di Perawat
        $kunjunganBudi = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienBudi->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-003',
            'keluhan' => 'Batuk dan pilek',
            'status' => 'menunggu_perawat',
        ]);

        $kunjunganMaya = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienMaya->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-004',
            'keluhan' => 'Alergi dan gatal-gatal',
            'status' => 'menunggu_perawat',
        ]);

        // KASUS 5 & 6: Di Dokter
        $kunjunganRizky = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienRizky->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-005',
            'keluhan' => 'Nyeri perut',
            'status' => 'menunggu_dokter',
        ]);

        $kunjunganLina = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienLina->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-006',
            'keluhan' => 'Tekanan darah tinggi',
            'status' => 'menunggu_dokter',
        ]);

        // KASUS 7 & 8: Di Kasir (Menunggu Pembayaran)
        $kunjunganAgus = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienAgus->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-007',
            'keluhan' => 'Luka di tangan',
            'status' => 'menunggu_pembayaran',
        ]);

        $kunjunganTari = Kunjungan::create([
            'id_klinik' => 1,
            'id_pasien' => $pasienTari->id,
            'id_dokter' => $stafDokter->id,
            'id_jadwal' => $jadwal->id,
            'tgl_kunjungan' => now()->toDateString(),
            'no_antrian' => 'A-008',
            'keluhan' => 'Kontrol rutin kesehatan',
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

        echo "Seeding Selesai! \n";
    }
}
