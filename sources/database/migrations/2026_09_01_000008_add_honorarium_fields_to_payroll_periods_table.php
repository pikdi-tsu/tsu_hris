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
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->string('kategori_honor', 30)->nullable()->after('tipe'); // kelebihan_sks, pembimbing_penguji, ujian
            $table->string('tahun_akademik', 20)->nullable()->after('kategori_honor'); // 2025/2026
            $table->string('semester', 10)->nullable()->after('tahun_akademik'); // Ganjil, Genap
            $table->integer('jumlah_pertemuan')->nullable()->after('semester'); // khusus 8A: misal 3 atau 4 pertemuan
            $table->string('bulan_honor', 20)->nullable()->after('jumlah_pertemuan'); // April, Mei, Juni, Juli, Agustus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn(['kategori_honor', 'tahun_akademik', 'semester', 'jumlah_pertemuan', 'bulan_honor']);
        });
    }
};
