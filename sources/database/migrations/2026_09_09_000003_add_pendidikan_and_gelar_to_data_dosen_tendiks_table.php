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
            $table->string('pendidikan_terakhir', 20)->nullable()->after('posisi');
            $table->string('gelar_s1', 50)->nullable()->after('pendidikan_terakhir');
            $table->string('pendidikan_s1', 150)->nullable()->after('gelar_s1');
            $table->string('gelar_s2', 50)->nullable()->after('pendidikan_s1');
            $table->string('pendidikan_s2', 150)->nullable()->after('gelar_s2');
            $table->string('gelar_s3', 50)->nullable()->after('pendidikan_s2');
            $table->string('pendidikan_s3', 150)->nullable()->after('gelar_s3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $table->dropColumn([
                'pendidikan_terakhir',
                'gelar_s1',
                'pendidikan_s1',
                'gelar_s2',
                'pendidikan_s2',
                'gelar_s3',
                'pendidikan_s3'
            ]);
        });
    }
};
