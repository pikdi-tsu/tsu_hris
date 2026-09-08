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
            if (!Schema::hasColumn('data_dosen_tendiks', 'tgl_bergabung')) {
                $table->date('tgl_bergabung')->nullable()->after('tgl_lahir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            if (Schema::hasColumn('data_dosen_tendiks', 'tgl_bergabung')) {
                $table->dropColumn('tgl_bergabung');
            }
        });
    }
};
