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
        Schema::create('lembur_karyawans', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke master_lemburs
            $table->unsignedBigInteger('id_mlembur');
            
            // Data Karyawan yang mengajukan
            $table->uuid('id_user');
            
            // Waktu Lembur
            $table->dateTime('tanggalmulai');
            $table->dateTime('tanggalselesai');
            
            // Keterangan Lembur
            $table->text('keterangan');
            
            // Approval Atasan
            $table->uuid('id_atasan')->nullable();
            $table->string('statusatasan', 20)->default('waiting'); // waiting, approved, rejected
            
            // Approval HRD
            $table->uuid('id_hrd')->nullable();
            $table->string('statushrd', 20)->default('waiting'); // waiting, approved, rejected
            
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            // Foreign keys
            $table->foreign('id_mlembur')->references('id')->on('master_lemburs')->onDelete('restrict');
            // Assuming data_dosen_tendiks table exists and has a uuid primary key
            $table->foreign('id_user')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
            $table->foreign('id_atasan')->references('id')->on('data_dosen_tendiks')->onDelete('set null');
            $table->foreign('id_hrd')->references('id')->on('data_dosen_tendiks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur_karyawans');
    }
};
