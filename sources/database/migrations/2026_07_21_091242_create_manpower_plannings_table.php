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
        Schema::create('manpower_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengaju')->nullable()->comment('User yang mengajukan');
            $table->foreignId('unit_id')->nullable()->comment('Divisi/Unit yang membutuhkan');
            $table->foreignId('jabatan_id')->nullable()->comment('Posisi yang diminta');
            $table->year('tahun')->comment('Tahun perencanaan');
            $table->integer('jumlah_kebutuhan')->default(0)->comment('Headcount yang diminta');
            $table->enum('tipe_pengajuan', ['Penambahan Baru', 'Penggantian', 'Perluasan Proyek'])->default('Penambahan Baru');
            $table->text('alasan')->nullable();
            $table->enum('status', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->text('keterangan_hrd')->nullable();
            $table->foreignId('approved_by')->nullable()->comment('HRD yang menyetujui');
            $table->dateTime('approval_date')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpower_plannings');
    }
};
