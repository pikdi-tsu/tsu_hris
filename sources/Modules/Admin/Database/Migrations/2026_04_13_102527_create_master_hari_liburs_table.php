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
        Schema::create('master_harilibur', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Kolom utama
            $table->date('tanggal')->unique(); // Unique agar tidak ada tanggal ganda saat di-sync berkali-kali
            $table->text('keterangan')->nullable();

            // Sesuaikan isi array Enum dengan kesepakatan tim (contoh: Nasional, Cuti Bersama, dll)
            $table->enum('status_libur', ['Nasional', 'Institusi', 'Cuti Bersama'])->default('Nasional');

            // Audit Trail
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            // Flag aktif/tidak aktif
            $table->enum('isactive', ['Y', 'N'])->default('Y');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_harilibur');
    }
};
