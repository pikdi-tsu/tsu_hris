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
        // 1. Master Data Onboarding & Offboarding (Dosen & Tendik)
        if (!Schema::hasTable('master_onboarding_offboardings')) {
            Schema::create('master_onboarding_offboardings', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tugas', 200);
                $table->enum('kategori', ['onboarding', 'offboarding'])->index();
                $table->enum('sasaran', ['semua', 'dosen', 'tendik'])->default('semua')->index();
                $table->integer('urutan')->default(0);
                $table->text('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Checklist Pelaksanaan Per Karyawan
        if (!Schema::hasTable('karyawan_onboarding_offboardings')) {
            Schema::create('karyawan_onboarding_offboardings', function (Blueprint $table) {
                $table->id();
                $table->char('data_dosen_tendik_id', 36);
                $table->unsignedBigInteger('master_onboarding_offboarding_id');
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->char('completed_by', 36)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index('data_dosen_tendik_id', 'idx_onoff_karyawan');
                $table->index('master_onboarding_offboarding_id', 'idx_onoff_master');

                $table->foreign('data_dosen_tendik_id', 'fk_onoff_karyawan')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('cascade');

                $table->foreign('master_onboarding_offboarding_id', 'fk_onoff_master')
                    ->references('id')
                    ->on('master_onboarding_offboardings')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_onboarding_offboardings');
        Schema::dropIfExists('master_onboarding_offboardings');
    }
};
