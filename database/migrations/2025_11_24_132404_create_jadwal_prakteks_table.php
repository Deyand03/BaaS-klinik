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
        Schema::create('jadwal_praktek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_staff')->constrained('staff')->cascadeOnDelete(); // Dokter
            $table->string('hari'); // Senin, Selasa...
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('kuota_harian')->default(10);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_praktek');
    }
};
