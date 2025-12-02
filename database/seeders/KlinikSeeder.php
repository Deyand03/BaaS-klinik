<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KlinikSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('klinik')->insert([
            [
                "nama" => "Klinik X Subhan",
                "slug" => "klinik-umum",
                // Ganti no_dana jadi info_pembayaran
                "info_pembayaran" => "DANA: 081223454567, BCA: 12345678",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            // ... (Lanjutkan untuk klinik lainnya dengan format sama)
            [
                "nama" => "Klinik Mata Sehat",
                "slug" => "klinik-mata",
                "info_pembayaran" => "DANA: 081223452345",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            // ... dst
        ]);
    }
}
