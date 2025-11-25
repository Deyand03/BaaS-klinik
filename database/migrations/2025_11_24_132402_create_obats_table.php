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
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_klinik')->constrained('klinik')->cascadeOnDelete();
            $table->string('nama_obat');
            $table->string('merk')->nullable();
            $table->integer('stok');
            $table->decimal('harga', 10, 2);
            $table->enum('satuan', ['strip', 'botol', 'pcs', 'tablet']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};
