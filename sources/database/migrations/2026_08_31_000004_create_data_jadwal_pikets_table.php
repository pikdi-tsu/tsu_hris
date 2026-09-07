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
        Schema::create('data_jadwal_pikets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('data_dosen_tendik_id', 36)->index();
            $table->string('pin', 50)->nullable()->index();
            $table->date('tanggal_piket')->index();
            $table->time('jam_mulai')->default('08:00:00');
            $table->time('jam_selesai')->default('12:00:00');
            $table->integer('target_durasi_menit')->default(240); // 4 Jam
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('data_dosen_tendik_id')
                ->references('id')
                ->on('data_dosen_tendiks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_jadwal_pikets');
    }
};
