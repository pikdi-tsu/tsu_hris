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
            if (!Schema::hasColumn('data_dosen_tendiks', 'master_shift_id')) {
                $table->foreignUuid('master_shift_id')->nullable()->after('pin_absensi')->constrained('master_shifts')->onDelete('set null');
            }
        });

        Schema::table('data_absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('data_absensi', 'master_shift_id')) {
                $table->char('master_shift_id', 36)->nullable()->after('scan_4')->index();
            }
            if (!Schema::hasColumn('data_absensi', 'durasi_kerja')) {
                $table->string('durasi_kerja')->nullable()->after('master_shift_id')->comment('Contoh: 08:27:00');
            }
            if (!Schema::hasColumn('data_absensi', 'durasi_menit')) {
                $table->integer('durasi_menit')->default(0)->after('durasi_kerja')->comment('Durasi kerja efektif dalam menit');
            }
            if (!Schema::hasColumn('data_absensi', 'akumulasi_validasi')) {
                $table->decimal('akumulasi_validasi', 3, 1)->default(0.0)->after('durasi_menit')->comment('1.0 jika valid, 0.0 jika tidak');
            }
            if (!Schema::hasColumn('data_absensi', 'keterangan_validasi')) {
                $table->string('keterangan_validasi')->nullable()->after('akumulasi_validasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_absensi', function (Blueprint $table) {
            $table->dropColumn(['master_shift_id', 'durasi_kerja', 'durasi_menit', 'akumulasi_validasi', 'keterangan_validasi']);
        });

        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            if (Schema::hasColumn('data_dosen_tendiks', 'master_shift_id')) {
                $table->dropForeign(['master_shift_id']);
                $table->dropColumn('master_shift_id');
            }
        });
    }
};
