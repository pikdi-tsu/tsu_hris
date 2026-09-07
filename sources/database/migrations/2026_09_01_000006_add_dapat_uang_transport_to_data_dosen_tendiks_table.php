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
        if (Schema::hasTable('data_dosen_tendiks') && !Schema::hasColumn('data_dosen_tendiks', 'dapat_uang_transport')) {
            Schema::table('data_dosen_tendiks', function (Blueprint $table) {
                $table->boolean('dapat_uang_transport')->default(1)->after('persen_gaji_pokok');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('data_dosen_tendiks') && Schema::hasColumn('data_dosen_tendiks', 'dapat_uang_transport')) {
            Schema::table('data_dosen_tendiks', function (Blueprint $table) {
                $table->dropColumn('dapat_uang_transport');
            });
        }
    }
};
