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
        Schema::table('master_units', function (Blueprint $table) {
            $table->uuid('kepala_jabatan_id')->nullable()->after('keterangan');
            $table->foreign('kepala_jabatan_id')->references('id')->on('master_jabatan_strukturals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_units', function (Blueprint $table) {
            $table->dropForeign(['kepala_jabatan_id']);
            $table->dropColumn('kepala_jabatan_id');
        });
    }
};
