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
        Schema::table('master_jabatan_strukturals', function (Blueprint $table) {
            $table->enum('is_unit_specific', ['Y', 'N'])->default('Y')->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_jabatan_strukturals', function (Blueprint $table) {
            $table->dropColumn('is_unit_specific');
        });
    }
};
