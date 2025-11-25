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
        Schema::create('pemeriksaan_mata', function (Blueprint $table) {
            $table->id();
            // Relasi ke Induk Rekam Medis
            $table->foreignId('id_rekam_medis')->constrained('rekam_medis')->cascadeOnDelete();

            // Data Spesifik Mata
            $table->string('visus_od')->nullable(); // Kanan
            $table->string('visus_os')->nullable(); // Kiri
            $table->string('koreksi_sphere')->nullable(); // Minus/Plus
            $table->string('koreksi_cylinder')->nullable();
            $table->string('axis')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_mata');
    }
};
