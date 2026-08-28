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
        $tableName = config('app.table.karyawan_jabatan_fungsionals');

        Schema::create($tableName, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('data_dosen_tendik_id')->index();
            $table->uuid('jabatan_fungsional_id')->index();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->string('sk_jabatan')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->timestamps();

            // Setup foreign keys (if you are enforcing referential integrity)
            // But usually in TSU it's handled via logic, so indices are enough.
            // If you want explicit foreign keys:
            // $table->foreign('data_dosen_tendik_id')->references('id')->on(config('app.table.data_dosen_tendiks'))->onDelete('cascade');
            // $table->foreign('jabatan_fungsional_id')->references('id')->on(config('app.table.master_jabatan_fungsionals'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('app.table.karyawan_jabatan_fungsionals'));
    }
};
