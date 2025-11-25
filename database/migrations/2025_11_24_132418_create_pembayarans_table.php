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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kunjungan')->constrained('kunjungan');
            $table->foreignId('id_staff')->constrained('staff'); // Siapa yang terima duit

            $table->decimal('total_biaya', 12, 2);
            $table->enum('metode_bayar', ['cash'])->default('cash');
            $table->enum('status', ['pending', 'lunas', 'batal'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
