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
        // 1. Tambah kolom status_karyawan_id
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->uuid('status_karyawan_id')->nullable()->after('status_karyawan');
        });

        // 2. Auto Seed & Mapping
        $statuses = DB::table('data_dosen_tendiks')
            ->select('status_karyawan')
            ->whereNotNull('status_karyawan')
            ->where('status_karyawan', '!=', '')
            ->distinct()
            ->pluck('status_karyawan');

        foreach ($statuses as $status) {
            // Cek apakah sudah ada di master (untuk mencegah duplikat saat retry)
            $existing = DB::table('master_status_karyawans')->where('nama_status', $status)->first();
            if (!$existing) {
                $id = Str::uuid()->toString();
                DB::table('master_status_karyawans')->insert([
                    'id' => $id,
                    'nama_status' => $status,
                    'is_active' => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id;
            }

            // Update karyawan
            DB::table('data_dosen_tendiks')
                ->where('status_karyawan', $status)
                ->update(['status_karyawan_id' => $id]);
        }

        // 3. Drop kolom lama dan set foreign key
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->dropColumn('status_karyawan');
            $table->foreign('status_karyawan_id')->references('id')->on('master_status_karyawans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['status_karyawan_id']);
            
            // Re-create old column
            $table->string('status_karyawan', 50)->nullable()->after('status_karyawan_id');
        });

        // Restore string data from master
        $karyawans = DB::table('data_dosen_tendiks')->whereNotNull('status_karyawan_id')->get();
        foreach ($karyawans as $k) {
            $master = DB::table('master_status_karyawans')->where('id', $k->status_karyawan_id)->first();
            if ($master) {
                DB::table('data_dosen_tendiks')->where('id', $k->id)->update([
                    'status_karyawan' => $master->nama_status
                ]);
            }
        }

        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->dropColumn('status_karyawan_id');
        });
    }
};
