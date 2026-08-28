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
            $table->string('pin_absensi')->nullable()->after('posisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->dropColumn('pin_absensi');
        });
    }
};
