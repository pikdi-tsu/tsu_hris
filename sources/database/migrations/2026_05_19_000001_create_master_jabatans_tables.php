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
        $tableDataDosenTendik = config('app.table.data_dosen_tendiks');
        $tableStruktural = config('app.table.master_jabatan_strukturals');
        $tableFungsional = config('app.table.master_jabatan_fungsionals');
        $tablePangkatGolongan = config('app.table.master_pangkat_golongans');
        $tableRiwayat = config('app.table.riwayat_jabatans');

        // 1. Master Jabatan Struktural
        Schema::create($tableStruktural, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_jabatan');
            $table->integer('periode_jabatan')->nullable()->comment('Lama masa jabatan dalam bulan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Master Jabatan Fungsional
        Schema::create($tableFungsional, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_jabatan');
            $table->integer('periode_jabatan')->nullable()->comment('Lama masa jabatan dalam bulan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 3. Master Pangkat Golongan
        Schema::create($tablePangkatGolongan, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_pangkat_golongan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 4. Riwayat Jabatan
        Schema::create($tableRiwayat, static function (Blueprint $table) use ($tableDataDosenTendik, $tableStruktural, $tableFungsional, $tablePangkatGolongan) {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_dosen_tendik_id')->constrained($tableDataDosenTendik)->onDelete('cascade');
            $table->enum('tipe_jabatan', ['struktural', 'fungsional']);
            
            $table->foreignUuid('jabatan_struktural_id')->nullable()->constrained($tableStruktural)->onDelete('set null');
            $table->foreignUuid('jabatan_fungsional_id')->nullable()->constrained($tableFungsional)->onDelete('set null');
            $table->foreignUuid('pangkat_golongan_id')->nullable()->constrained($tablePangkatGolongan)->onDelete('set null');
            
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->integer('lama_menjabat_bulan')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('app.table.riwayat_jabatans'));
        Schema::dropIfExists(config('app.table.master_pangkat_golongans'));
        Schema::dropIfExists(config('app.table.master_jabatan_fungsionals'));
        Schema::dropIfExists(config('app.table.master_jabatan_strukturals'));
    }
};
