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
        $tableKaryawan = 'data_dosen_tendiks';
        $tableFungsionalPivot = 'karyawan_jabatan_fungsionals';

        // 1. Tambahkan kolom ke pivot
        if (!Schema::hasColumn($tableFungsionalPivot, 'pangkat_golongan_id')) {
            Schema::table($tableFungsionalPivot, function (Blueprint $table) {
                $table->uuid('pangkat_golongan_id')->nullable()->after('jabatan_fungsional_id');
            });
        }

        // 2. Data transfer dari data_dosen_tendiks ke pivot
        $oldData = DB::table($tableKaryawan)
            ->whereNotNull('pangkat_jabatan_fungsional_id')
            ->get();

        foreach ($oldData as $row) {
            // Update baris fungsional aktif milik user ini
            DB::table($tableFungsionalPivot)
                ->where('data_dosen_tendik_id', $row->id)
                ->where('is_active', 'Y')
                ->update([
                    'pangkat_golongan_id' => $row->pangkat_jabatan_fungsional_id
                ]);
        }

        // 3. Drop kolom lama dengan aman
        if (Schema::hasColumn($tableKaryawan, 'pangkat_jabatan_fungsional_id')) {
            Schema::table($tableKaryawan, function (Blueprint $table) {
                $table->dropColumn('pangkat_jabatan_fungsional_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableKaryawan = 'data_dosen_tendiks';
        $tableFungsionalPivot = 'karyawan_jabatan_fungsionals';

        if (!Schema::hasColumn($tableKaryawan, 'pangkat_jabatan_fungsional_id')) {
            Schema::table($tableKaryawan, function (Blueprint $table) {
                $table->uuid('pangkat_jabatan_fungsional_id')->nullable();
            });
        }

        if (Schema::hasColumn($tableFungsionalPivot, 'pangkat_golongan_id')) {
            Schema::table($tableFungsionalPivot, function (Blueprint $table) {
                $table->dropColumn('pangkat_golongan_id');
            });
        }
    }
};
