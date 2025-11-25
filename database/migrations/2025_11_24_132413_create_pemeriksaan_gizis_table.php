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
        Schema::create('pemeriksaan_gizi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rekam_medis')->constrained('rekam_medis')->cascadeOnDelete();

            // Data Spesifik Gizi
            $table->float('tinggi_badan');
            $table->float('berat_badan');
            $table->float('imt')->nullable(); // Indeks Massa Tubuh
            $table->float('lingkar_perut')->nullable();
            $table->string('status_gizi')->nullable(); // Normal/Obesitas

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_gizi');
    }
};
