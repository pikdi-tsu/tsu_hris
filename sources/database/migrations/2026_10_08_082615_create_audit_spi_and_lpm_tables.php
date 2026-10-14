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
        // 1. Audit SPI Temuans
        if (!Schema::hasTable('audit_spi_temuans')) {
            Schema::create('audit_spi_temuans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kode_temuan')->unique(); // e.g. SPI-2026-001
                $table->uuid('unit_id')->nullable();
                $table->string('unit_nama')->nullable(); // SDM, BAUK, LPPM, etc.
                $table->date('tanggal_audit');
                $table->string('area_audit'); // Administrasi Kepegawaian, Pengelolaan Aset, etc.
                $table->text('temuan');
                $table->string('kategori')->nullable(); // Ketidakpatuhan Administrasi, Ketidaksesuaian Proses, etc.
                $table->enum('tingkat_risiko', ['Tinggi', 'Sedang', 'Rendah'])->default('Sedang');
                $table->text('rekomendasi')->nullable();
                $table->string('pic')->nullable(); // SDM, BAUK, etc.
                $table->date('deadline')->nullable();
                $table->enum('status', ['Terbuka', 'Dalam Proses', 'Selesai', 'Overdue'])->default('Terbuka');
                $table->string('bukti_tindak_lanjut')->nullable();
                $table->text('catatan_tindak_lanjut')->nullable();
                $table->json('riwayat_status')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Audit LPM Temuans
        if (!Schema::hasTable('audit_lpm_temuans')) {
            Schema::create('audit_lpm_temuans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kode_temuan')->unique(); // e.g. LPM-2026-001
                $table->uuid('unit_id')->nullable();
                $table->string('unit_nama')->nullable(); // Fakultas Ekonomi, FTI, LPPM, etc.
                $table->date('tanggal_audit');
                $table->string('standar_mutu'); // Standar SDM, Standar Pembelajaran, etc.
                $table->string('aspek')->nullable(); // Kecukupan SDM, Kualitas Pembelajaran, etc.
                $table->string('indikator')->nullable(); // Rasio dosen/mahasiswa, EDOM, etc.
                $table->string('target_standar')->nullable(); // e.g. 1:30
                $table->text('kondisi_aktual')->nullable(); // e.g. 1:40
                $table->text('temuan');
                $table->text('analisis_penyebab')->nullable();
                $table->text('rekomendasi')->nullable();
                $table->string('pic')->nullable();
                $table->date('deadline')->nullable();
                $table->enum('status', ['Terbuka', 'Dalam Proses', 'Selesai', 'Overdue'])->default('Terbuka');
                $table->string('bukti_tindak_lanjut')->nullable();
                $table->text('catatan_tindak_lanjut')->nullable();
                $table->json('riwayat_status')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Survey Pelatihan Responses additions
        if (Schema::hasTable('survey_pelatihan_responses')) {
            Schema::table('survey_pelatihan_responses', function (Blueprint $table) {
                if (!Schema::hasColumn('survey_pelatihan_responses', 'skor_metode')) {
                    $table->unsignedTinyInteger('skor_metode')->default(5)->after('skor_narasumber');
                }
                if (!Schema::hasColumn('survey_pelatihan_responses', 'skor_manfaat')) {
                    $table->unsignedTinyInteger('skor_manfaat')->default(5)->after('feedback_manfaat');
                }
                if (!Schema::hasColumn('survey_pelatihan_responses', 'saran_masukan')) {
                    $table->text('saran_masukan')->nullable()->after('skor_manfaat');
                }
                if (!Schema::hasColumn('survey_pelatihan_responses', 'bukti_file')) {
                    $table->string('bukti_file')->nullable()->after('saran_masukan');
                }
                if (!Schema::hasColumn('survey_pelatihan_responses', 'is_draft')) {
                    $table->boolean('is_draft')->default(false)->after('bukti_file');
                }
            });
        }

        // 4. KPI Unit Indikators additions
        if (Schema::hasTable('kpi_unit_indikators')) {
            Schema::table('kpi_unit_indikators', function (Blueprint $table) {
                if (!Schema::hasColumn('kpi_unit_indikators', 'formula_pengukuran')) {
                    $table->string('formula_pengukuran')->nullable()->after('jenis_cascading');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_lpm_temuans');
        Schema::dropIfExists('audit_spi_temuans');

        if (Schema::hasTable('survey_pelatihan_responses')) {
            Schema::table('survey_pelatihan_responses', function (Blueprint $table) {
                if (Schema::hasColumn('survey_pelatihan_responses', 'skor_metode')) {
                    $table->dropColumn('skor_metode');
                }
                if (Schema::hasColumn('survey_pelatihan_responses', 'skor_manfaat')) {
                    $table->dropColumn('skor_manfaat');
                }
                if (Schema::hasColumn('survey_pelatihan_responses', 'saran_masukan')) {
                    $table->dropColumn('saran_masukan');
                }
                if (Schema::hasColumn('survey_pelatihan_responses', 'bukti_file')) {
                    $table->dropColumn('bukti_file');
                }
                if (Schema::hasColumn('survey_pelatihan_responses', 'is_draft')) {
                    $table->dropColumn('is_draft');
                }
            });
        }

        if (Schema::hasTable('kpi_unit_indikators')) {
            Schema::table('kpi_unit_indikators', function (Blueprint $table) {
                if (Schema::hasColumn('kpi_unit_indikators', 'formula_pengukuran')) {
                    $table->dropColumn('formula_pengukuran');
                }
            });
        }
    }
};
