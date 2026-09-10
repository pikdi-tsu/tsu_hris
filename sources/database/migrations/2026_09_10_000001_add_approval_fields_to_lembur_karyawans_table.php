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
        Schema::table('lembur_karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('lembur_karyawans', 'alasanatasan')) {
                $table->text('alasanatasan')->nullable()->after('statusatasan');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'atasanapprovaldate')) {
                $table->dateTime('atasanapprovaldate')->nullable()->after('alasanatasan');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'alasanhrd')) {
                $table->text('alasanhrd')->nullable()->after('statushrd');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'hrdapprovaldate')) {
                $table->dateTime('hrdapprovaldate')->nullable()->after('alasanhrd');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembur_karyawans', function (Blueprint $table) {
            $table->dropColumn(['alasanatasan', 'atasanapprovaldate', 'alasanhrd', 'hrdapprovaldate']);
        });
    }
};
