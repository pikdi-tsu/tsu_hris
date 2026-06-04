<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableKaryawan = config('app.table.data_dosen_tendiks');
        $tablePivot = config('app.table.karyawan_jabatan_fungsionals');

        // 1. Pindahkan data lama ke tabel pivot
        $oldData = DB::table($tableKaryawan)
            ->whereNotNull('jabatan_fungsional_id')
            ->get();

        $pivotInserts = [];
        foreach ($oldData as $row) {
            // Deteksi multiple UUID (cth format: ["id1", "id2"], atau id1,id2)
            $stringIds = $row->jabatan_fungsional_id;
            preg_match_all('/[a-f0-9\-]{36}/i', $stringIds, $matches);
            $extractedIds = $matches[0] ?? [];

            if (empty($extractedIds)) {
                // Fallback jika ternyata bukan UUID standar tapi string ID lain
                $extractedIds = [$stringIds];
            }

            foreach ($extractedIds as $f_id) {
                // Bersihkan quote/bracket dari ID fallback jika ada
                $f_id = trim($f_id, '[]"\' ');
                
                if (empty($f_id)) continue;

                $pivotInserts[] = [
                    'id' => Str::uuid()->toString(),
                    'data_dosen_tendik_id' => $row->id,
                    'jabatan_fungsional_id' => $f_id,
                    'tgl_mulai' => $row->tmt_jabatan_fungsional ?? null,
                    'tgl_akhir' => $row->tgl_akhir_jabatan_fungsional ?? null,
                    'sk_jabatan' => $row->sk_jabatan_fungsional ?? null,
                    'is_active' => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($pivotInserts) > 0) {
            // Insert in chunks to avoid memory limit or query size limits if data is huge
            foreach (array_chunk($pivotInserts, 500) as $chunk) {
                DB::table($tablePivot)->insert($chunk);
            }
        }

        // 2. Hapus kolom lama dengan mengecek apakah kolom eksis
        $colsToDrop = [];
        $possibleCols = [
            'jabatan_fungsional_id',
            'tmt_jabatan_fungsional',
            'tgl_akhir_jabatan_fungsional',
            'sk_jabatan_fungsional'
        ];
        
        foreach ($possibleCols as $col) {
            if (Schema::hasColumn($tableKaryawan, $col)) {
                $colsToDrop[] = $col;
            }
        }
        
        if (!empty($colsToDrop)) {
            Schema::table($tableKaryawan, function (Blueprint $table) use ($colsToDrop) {
                $table->dropColumn($colsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableKaryawan = config('app.table.data_dosen_tendiks');

        Schema::table($tableKaryawan, function (Blueprint $table) {
            $table->foreignUuid('jabatan_fungsional_id')->nullable()->constrained(config('app.table.master_jabatan_fungsionals'))->onDelete('set null');
            $table->date('tmt_jabatan_fungsional')->nullable();
            $table->date('tgl_akhir_jabatan_fungsional')->nullable();
            $table->string('sk_jabatan_fungsional')->nullable();
        });
        
        // Note: Reversing data migration is possible but usually not fully implemented.
        // We'll leave it empty to avoid complex reverse logic unless needed.
    }
};
