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
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->uuid('unit_id')->nullable()->after('id');
            $table->string('tipe_karyawan')->nullable()->after('nama')->comment('Dosen / Tendik');
            $table->string('posisi')->nullable()->after('tipe_karyawan')->comment('Nama pekerjaan fungsional harian');
            
            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'tipe_karyawan', 'posisi']);
        });
    }
};
