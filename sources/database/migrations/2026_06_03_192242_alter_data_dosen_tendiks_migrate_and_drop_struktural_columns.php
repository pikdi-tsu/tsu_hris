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
        $tablePivot = config('app.table.karyawan_jabatan_strukturals');

        // 1. Pindahkan data lama ke tabel pivot
        $oldData = DB::table($tableKaryawan)
            ->whereNotNull('jabatan_struktural_id')
            ->get();

        $pivotInserts = [];
        foreach ($oldData as $row) {
            // Deteksi multiple UUID (cth format: ["id1", "id2"], atau id1,id2)
            $stringIds = $row->jabatan_struktural_id;
            preg_match_all('/[a-f0-9\-]{36}/i', $stringIds, $matches);
            $extractedIds = $matches[0] ?? [];

            if (empty($extractedIds)) {
                $extractedIds = [$stringIds];
            }

            foreach ($extractedIds as $s_id) {
                $s_id = trim($s_id, '[]"\' ');
                
                if (empty($s_id)) continue;

                $pivotInserts[] = [
                    'id' => Str::uuid()->toString(),
                    'data_dosen_tendik_id' => $row->id,
                    'jabatan_struktural_id' => $s_id,
                    'tgl_mulai' => $row->tgl_mulai_jabatan_struktural ?? null,
                    'tgl_akhir' => $row->tgl_akhir_jabatan_struktural ?? null,
                    'is_active' => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($pivotInserts) > 0) {
            foreach (array_chunk($pivotInserts, 500) as $chunk) {
                DB::table($tablePivot)->insert($chunk);
            }
        }

        // 2. Hapus kolom lama (tanpa dropForeign karena key-nya sudah tidak ada) dengan mengecek apakah kolom eksis
        $colsToDrop = [];
        $possibleCols = [
            'jabatan_struktural_id',
            'tgl_mulai_jabatan_struktural',
            'tgl_akhir_jabatan_struktural',
            'periode_jabatan_struktural'
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
            $table->foreignUuid('jabatan_struktural_id')->nullable()->constrained(config('app.table.master_jabatan_strukturals'))->onDelete('set null');
            $table->date('tgl_mulai_jabatan_struktural')->nullable();
            $table->date('tgl_akhir_jabatan_struktural')->nullable();
        });
    }
};
