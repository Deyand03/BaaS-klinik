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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kunjungan')->constrained('kunjungan')->cascadeOnDelete();
            $table->text('anamnesa')->nullable();
            $table->text('diagnosa')->nullable();
            $table->text('tindakan')->nullable();
            $table->text('catatan_dokter')->nullable();

            // Tanda Vital Dasar
            $table->string('tensi_darah')->nullable();
            $table->float('berat_badan')->nullable();
            $table->float('suhu_badan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
