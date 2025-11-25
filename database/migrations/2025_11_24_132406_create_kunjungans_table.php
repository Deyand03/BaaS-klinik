<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_klinik')->constrained('klinik');
            $table->foreignId('id_pasien')->constrained('profil_pasien');
            $table->foreignId('id_dokter')->constrained('staff');
            $table->foreignId('id_jadwal')->nullable()->constrained('jadwal_praktek');

            $table->date('tgl_kunjungan');
            $table->string('no_antrian'); // A-001
            $table->text('keluhan')->nullable();
            // Status sudah update sesuai request: ada 'check_in'
            $table->enum('status', ['booking', 'check_in', 'diperiksa', 'selesai', 'batal'])->default('booking');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
