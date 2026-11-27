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
        Schema::table('pegawai_kontraks', function (Blueprint $table) {
            $table->string('jenis_hubungan_kerja', 20)->default('pkwt')->after('pegawai_id')->index(); // pkl, magang, pkwt, pkwtt
            $table->string('institusi_asal', 150)->nullable()->after('durasi_bulan');
            $table->string('pembimbing_lapangan', 150)->nullable()->after('institusi_asal');
            $table->string('no_sk_pengangkatan', 100)->nullable()->after('pembimbing_lapangan');
            $table->date('tmt_tetap')->nullable()->after('no_sk_pengangkatan');
            $table->string('no_kontrak', 100)->nullable()->change();
            $table->date('tgl_selesai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai_kontraks', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_hubungan_kerja',
                'institusi_asal',
                'pembimbing_lapangan',
                'no_sk_pengangkatan',
                'tmt_tetap',
            ]);
        });
    }
};
