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
        $tableName = config('app.table.data_dosen_tendiks');
        $tableStruktural = config('app.table.master_jabatan_strukturals');
        $tableFungsional = config('app.table.master_jabatan_fungsionals');
        $tablePangkatGolongan = config('app.table.master_pangkat_golongans');

        // Schema::table($tableName, static function (Blueprint $table) use ($tableStruktural, $tableFungsional, $tablePangkatGolongan) {
        //     // Drop old string columns
        //     $table->dropColumn([
        //         'jabatan_struktural',
        //         'periode_jabatan_struktural',
        //         'jabatan_fungsional',
        //         'pangkat_jabatan_fungsional'
        //     ]);

        //     // Add new relation columns and tracking
        //     $table->foreignUuid('jabatan_struktural_id')->nullable()->after('alamat_domisili')->constrained($tableStruktural)->onDelete('set null');
        //     $table->date('tgl_akhir_jabatan_struktural')->nullable()->after('tgl_mulai_jabatan_struktural');

        //     $table->foreignUuid('jabatan_fungsional_id')->nullable()->after('tgl_akhir_jabatan_struktural')->constrained($tableFungsional)->onDelete('set null');
        //     $table->foreignUuid('pangkat_golongan_id')->nullable()->after('jabatan_fungsional_id')->constrained($tablePangkatGolongan)->onDelete('set null');
        //     $table->date('tgl_akhir_jabatan_fungsional')->nullable()->after('tmt_jabatan_fungsional');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('app.table.data_dosen_tendiks');
        $tableStruktural = config('app.table.master_jabatan_strukturals');
        $tableFungsional = config('app.table.master_jabatan_fungsionals');
        $tablePangkatGolongan = config('app.table.master_pangkat_golongans');

        // Schema::table($tableName, static function (Blueprint $table) use ($tableStruktural, $tableFungsional, $tablePangkatGolongan) {
        //     // Drop foreign keys first (needs correct index names ideally, but dropping columns also works if DBMS allows, but Laravel constrained needs explicit drop on some DBs)
        //     // It's safer to just drop constrained columns
        //     $table->dropForeign(['jabatan_struktural_id']);
        //     $table->dropForeign(['jabatan_fungsional_id']);
        //     $table->dropForeign(['pangkat_golongan_id']);

        //     $table->dropColumn([
        //         'jabatan_struktural_id',
        //         'tgl_akhir_jabatan_struktural',
        //         'jabatan_fungsional_id',
        //         'pangkat_golongan_id',
        //         'tgl_akhir_jabatan_fungsional'
        //     ]);

        //     // Re-add old string columns
        //     $table->string('jabatan_struktural')->nullable()->after('alamat_domisili');
        //     $table->string('periode_jabatan_struktural')->nullable()->after('tgl_mulai_jabatan_struktural');
        //     $table->string('jabatan_fungsional')->nullable()->after('periode_jabatan_struktural');
        //     $table->string('pangkat_jabatan_fungsional')->nullable()->after('jabatan_fungsional');
        // });
    }
};
