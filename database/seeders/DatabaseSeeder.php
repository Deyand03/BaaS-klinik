<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Klinik;
use App\Models\Staff;
use App\Models\JadwalPraktek;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bikin Klinik Umum (ID 1)
        // Pastikan ID ini 1, karena frontend hardcode ID 1
        $this->call([
            KlinikSeeder::class,
        ]);
        // ---------------------------------------------------------

        // 3. Bikin User untuk Dokter (Generic Role: 'staff')
        $userDokter = User::create([
            'name' => 'Dr. Budi', // Opsional, krn nama asli ada di tabel staf
            'email' => 'dokter@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff', // <--- INI YANG KITA SEPAKATI (Generic)
        ]);

        // 4. Bikin Profil Staf (Specific Role: 'dokter')
        $stafDokter = Staff::create([
            'user_id' => $userDokter->id,
            'id_klinik' => 1,
            'nama' => 'Dr. Budi Santoso',
            'peran' => 'dokter', // <--- INI DETAIL JABATANNYA
            'spesialisasi' => 'Dokter Umum',
        ]);

        // ---------------------------------------------------------

        // 5. Bikin User untuk Admin Klinik (Generic Role: 'staff')
        $userAdmin = User::create([
            'name' => 'Admin Siti',
            'email' => 'admin@umum.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 6. Bikin Profil Staf (Specific Role: 'admin')
        Staff::create([
            'user_id' => $userAdmin->id,
            'id_klinik' => 1,
            'nama' => 'Siti Aminah',
            'peran' => 'admin', // <--- DETAIL JABATAN
            'spesialisasi' => null,
        ]);

        // ---------------------------------------------------------

        // 7. Bikin Jadwal Praktek Dokter Budi
        JadwalPraktek::create([
            'id_staff' => $stafDokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'status_aktif' => true,
        ]);

        echo "Data Dummy (Revisi) Berhasil Dibuat! \n";
    }
}
