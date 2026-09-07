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
        Schema::create('master_komponen_presensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_komponen')->comment('Contoh: Uang Transport, Uang Makan, dll');
            $table->string('kode_komponen')->nullable();
            $table->enum('kategori', ['transport', 'makan', 'tunjangan_kehadiran', 'lainnya'])->default('transport');
            $table->decimal('nominal', 15, 2)->default(20000.00)->comment('Nominal per hari kehadiran valid');
            $table->enum('satuan', ['per_kehadiran', 'per_hari', 'per_bulan'])->default('per_kehadiran');
            $table->text('keterangan')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_komponen_presensis');
    }
};
