<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KlinikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan tabel kosong dulu biar gak duplikat kalau di-seed ulang
        // DB::table('klinik')->truncate(); // Hati-hati, uncomment ini kalau mau hapus data lama

        DB::table('klinik')->insert([
            // 1. Klinik Umum (Utama)
            [
                "nama" => "Klinik X Subhan",
                "slug" => "klinik-umum",
                "info_pembayaran" => "DANA: 081223454567, BCA: 12345678",
                "alamat" => "Mendalo Indah, Jambi Luar Kota",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // 2. Klinik Mata
            [
                "nama" => "Klinik Mata Sehat",
                "slug" => "klinik-mata",
                "info_pembayaran" => "DANA: 081223452345, MANDIRI: 1300098765",
                "alamat" => "Jl. Kacamata No. 12, Telanaipura",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // 3. Klinik Gigi
            [
                "nama" => "Klinik Gigi Senyum",
                "slug" => "klinik-gigi",
                "info_pembayaran" => "GOPAY: 081345678901, BNI: 0987654321",
                "alamat" => "Simpang Rimbo, Kota Jambi",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // 4. Klinik Gizi
            [
                "nama" => "Klinik Gizi",
                "slug" => "klinik-gizi",
                "info_pembayaran" => "QRIS: Tersedia di Kasir, BRI: 3322114455",
                "alamat" => "Thehok, Jambi Selatan",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // 5. Klinik Anak
            [
                "nama" => "Klinik Kulit&Kelamin",
                "slug" => "klinik-kumin",
                "info_pembayaran" => "CASH ONLY (Sementara), BSI: 7788990011",
                "alamat" => "Mayang Mangurai, Kota Baru",
                "logo" => "placeholder.png",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
