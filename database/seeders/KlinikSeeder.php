<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KlinikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('klinik')->insert([
            [
                "nama" => "Klinik X Subhan",
                "slug" => "klinik-umum",
                "no_dana" => "081223454567",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-mata",
                "no_dana" => "0812234523451",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-gizi",
                "no_dana" => "081223454631",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-gigi",
                "no_dana" => "0812234534612",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-kumin",
                "no_dana" => "08122345123123",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
        ]);
    }
}
