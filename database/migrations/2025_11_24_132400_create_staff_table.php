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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_klinik')->constrained('klinik')->cascadeOnDelete();
            $table->string('nama_lengkap'); // Ganti 'nama'
            $table->enum('peran', ['dokter', 'admin', 'perawat', 'resepsionis', 'kasir']);
            $table->string('no_hp')->nullable();
            $table->string('spesialisasi')->nullable(); // Khusus dokter
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
