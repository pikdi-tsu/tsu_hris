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
        Schema::create('master_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_shift');
            $table->string('kode_shift')->nullable();
            $table->enum('tipe_shift', ['jadwal', 'durasi'])->default('jadwal')->comment('jadwal: jam masuk & pulang tetap; durasi: target durasi kerja per hari (Dosen/Struktural)');
            $table->integer('target_durasi_menit')->default(420)->comment('Target durasi kerja per hari dalam menit');
            $table->text('keterangan')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });

        Schema::create('master_shift_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('master_shift_id')->constrained('master_shifts')->onDelete('cascade');
            $table->tinyInteger('hari')->comment('1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->time('jam_istirahat_mulai')->nullable();
            $table->time('jam_istirahat_selesai')->nullable();
            $table->boolean('is_cross_day')->default(false)->comment('True jika shift malam melewati tengah malam');
            $table->boolean('is_libur')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_shift_details');
        Schema::dropIfExists('master_shifts');
    }
};
