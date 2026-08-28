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
        $tableName = config('app.table.karyawan_jabatan_strukturals');

        Schema::create($tableName, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('data_dosen_tendik_id')->index();
            $table->uuid('jabatan_struktural_id')->index();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('app.table.karyawan_jabatan_strukturals'));
    }
};
