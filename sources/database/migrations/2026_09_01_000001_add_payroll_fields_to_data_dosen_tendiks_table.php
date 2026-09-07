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
        $tableName = 'data_dosen_tendiks';

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('nama_bank', 50)->nullable()->default('Bank Mandiri')->after('status_perkawinan');
            $table->string('no_rekening', 50)->nullable()->after('nama_bank');
            $table->string('atas_nama_rekening', 150)->nullable()->after('no_rekening');
            $table->tinyInteger('persen_gaji_pokok')->default(100)->after('atas_nama_rekening')->comment('100 atau 80 persen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'data_dosen_tendiks';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'no_rekening', 'atas_nama_rekening', 'persen_gaji_pokok']);
        });
    }
};
