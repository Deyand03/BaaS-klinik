<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // --- TAMBAHAN WAJIB ---
            $table->string('nama_lengkap'); // <--- INI PENTING BANGET!
            // ----------------------

            $table->string('nik', 16)->unique();
            $table->date('tgl_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']); // Saran: Pakai 'L'/'P' biar hemat storage, tapi string panjang juga gapapa.
            $table->text('alamat_domisili');
            $table->string('no_hp')->nullable();
            $table->string('golongan_darah', 5)->nullable(); // kasih limit panjang biar rapi (A, B, AB, O)
            $table->string('riwayat_alergi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};
