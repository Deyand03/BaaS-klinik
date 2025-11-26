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
            $table->foreignId('id_rekam_medis')->constrained('rekam_medis')->cascadeOnDelete();

            // --- MATA KANAN (OD) ---
            $table->string('visus_od')->nullable();      // Ketajaman Penglihatan
            $table->string('sphere_od')->nullable();     // Spheris (Minus/Plus)
            $table->string('cylinder_od')->nullable();   // Silinder
            $table->string('axis_od')->nullable();       // Sumbu Silinder

            // --- MATA KIRI (OS) ---
            $table->string('visus_os')->nullable();
            $table->string('sphere_os')->nullable();
            $table->string('cylinder_os')->nullable();
            $table->string('axis_os')->nullable();

            $table->string('pd')->nullable(); // Pupillary Distance (Jarak Pupil)

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
