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
        if (!Schema::hasTable('riwayat_perubahan_data')) {
            Schema::create('riwayat_perubahan_data', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('data_dosen_tendik_id')->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('kategori_perubahan', 100)->default('Data Pribadi');
                $table->json('perubahan_data')->comment('Format old & new values');
                $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->index();
                $table->text('catatan_pemohon')->nullable();
                $table->uuid('disetujui_oleh_user_id')->nullable();
                $table->text('catatan_sdm')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('data_dosen_tendik_id')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_data');
    }
};
