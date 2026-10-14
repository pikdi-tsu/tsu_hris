<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. data_absensi
        if (Schema::hasTable('data_absensi')) {
            Schema::table('data_absensi', function (Blueprint $table) {
                $this->addIndexIfNotExists('data_absensi', 'idx_absensi_pin_tgl', ['pin', 'tanggal_absen']);
                $this->addIndexIfNotExists('data_absensi', 'idx_absensi_periode', ['periode_tahun', 'periode_bulan']);
            });
        }

        // 2. cuti_karyawan
        if (Schema::hasTable('cuti_karyawan')) {
            Schema::table('cuti_karyawan', function (Blueprint $table) {
                $this->addIndexIfNotExists('cuti_karyawan', 'idx_cuti_user_active', ['id_user', 'is_active']);
                $this->addIndexIfNotExists('cuti_karyawan', 'idx_cuti_tgl_rentang', ['tanggalmulai', 'tanggalselesai']);
            });
        }

        // 3. izin_karyawan
        if (Schema::hasTable('izin_karyawan')) {
            Schema::table('izin_karyawan', function (Blueprint $table) {
                $this->addIndexIfNotExists('izin_karyawan', 'idx_izin_user_active', ['id_user', 'is_active']);
                $this->addIndexIfNotExists('izin_karyawan', 'idx_izin_tgl_rentang', ['tanggalmulai', 'tanggalselesai']);
            });
        }

        // 4. saldo_cuti_karyawan
        if (Schema::hasTable('saldo_cuti_karyawan')) {
            Schema::table('saldo_cuti_karyawan', function (Blueprint $table) {
                $this->addIndexIfNotExists('saldo_cuti_karyawan', 'idx_saldo_user_tahun', ['id_user', 'tahun']);
            });
        }

        // 5. payroll_karyawans
        if (Schema::hasTable('payroll_karyawans')) {
            Schema::table('payroll_karyawans', function (Blueprint $table) {
                $this->addIndexIfNotExists('payroll_karyawans', 'idx_payroll_period_pegawai', ['payroll_period_id', 'data_dosen_tendik_id']);
            });
        }

        // 6. laporan_kegiatan_sdms
        if (Schema::hasTable('laporan_kegiatan_sdms')) {
            Schema::table('laporan_kegiatan_sdms', function (Blueprint $table) {
                $this->addIndexIfNotExists('laporan_kegiatan_sdms', 'idx_laporan_sdm_status_tgl', ['status', 'tanggal_mulai']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('data_absensi')) {
            Schema::table('data_absensi', function (Blueprint $table) {
                $this->dropIndexIfExists('data_absensi', 'idx_absensi_pin_tgl');
                $this->dropIndexIfExists('data_absensi', 'idx_absensi_periode');
            });
        }

        if (Schema::hasTable('cuti_karyawan')) {
            Schema::table('cuti_karyawan', function (Blueprint $table) {
                $this->dropIndexIfExists('cuti_karyawan', 'idx_cuti_user_active');
                $this->dropIndexIfExists('cuti_karyawan', 'idx_cuti_tgl_rentang');
            });
        }

        if (Schema::hasTable('izin_karyawan')) {
            Schema::table('izin_karyawan', function (Blueprint $table) {
                $this->dropIndexIfExists('izin_karyawan', 'idx_izin_user_active');
                $this->dropIndexIfExists('izin_karyawan', 'idx_izin_tgl_rentang');
            });
        }

        if (Schema::hasTable('saldo_cuti_karyawan')) {
            Schema::table('saldo_cuti_karyawan', function (Blueprint $table) {
                $this->dropIndexIfExists('saldo_cuti_karyawan', 'idx_saldo_user_tahun');
            });
        }

        if (Schema::hasTable('payroll_karyawans')) {
            Schema::table('payroll_karyawans', function (Blueprint $table) {
                $this->dropIndexIfExists('payroll_karyawans', 'idx_payroll_period_pegawai');
            });
        }

        if (Schema::hasTable('laporan_kegiatan_sdms')) {
            Schema::table('laporan_kegiatan_sdms', function (Blueprint $table) {
                $this->dropIndexIfExists('laporan_kegiatan_sdms', 'idx_laporan_sdm_status_tgl');
            });
        }
    }

    protected function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($existing)) {
            $colsStr = '`' . implode('`, `', $columns) . '`';
            DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$colsStr})");
        }
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (!empty($existing)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        }
    }
};
