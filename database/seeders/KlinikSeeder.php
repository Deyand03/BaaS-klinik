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
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-mata",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-gizi",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-gigi",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
            [
                "nama" => "klinik",
                "slug" => "klinik-kumin",
                "alamat" => "Mendalo Indah",
                "logo" => "placeholder.png",
            ],
        ]);
    }
}
