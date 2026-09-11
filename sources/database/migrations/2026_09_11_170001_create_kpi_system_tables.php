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
        // 1. Tabel Periode KPI
        if (!Schema::hasTable('kpi_periodes')) {
            Schema::create('kpi_periodes', function (Blueprint $table) {
                $table->id();
                $table->integer('tahun')->unique();
                $table->string('nama_periode', 100);
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('is_active')->default(false)->index();
                $table->boolean('is_locked')->default(false)->index();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel 4 Perspektif Balanced Scorecard (BSC)
        if (!Schema::hasTable('kpi_master_perspektifs')) {
            Schema::create('kpi_master_perspektifs', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 20)->unique(); // FIN, CUS, INT, LRN
                $table->string('nama_perspektif', 100);
                $table->text('deskripsi')->nullable();
                $table->string('warna_badge', 30)->default('primary');
                $table->integer('urutan')->default(1);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 3. Tabel Master Kamus Indikator (Hierarkis Induk - Sub)
        if (!Schema::hasTable('kpi_master_indikators')) {
            Schema::create('kpi_master_indikators', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('perspektif_id');
                $table->unsignedBigInteger('parent_id')->nullable(); // Self-reference hierarki (Pendekatan 1)
                $table->enum('level', ['induk', 'sub'])->default('induk');
                $table->string('kode_indikator', 50)->unique();
                $table->string('nama_indikator', 255);
                $table->text('deskripsi')->nullable();
                $table->string('satuan', 50)->default('%');
                $table->string('polaritas', 30)->default('Maximize'); // Maximize, Minimize, Stabilize
                $table->string('tipe_target', 30)->default('Angka'); // Angka, Persentase, Rupiah, Waktu, Skala
                $table->text('formula_penghitungan')->nullable();
                $table->integer('urutan')->default(1);
                $table->boolean('is_active')->default(true)->index();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('perspektif_id', 'fk_kpi_mind_persp')
                    ->references('id')->on('kpi_master_perspektifs')
                    ->onDelete('cascade');

                $table->foreign('parent_id', 'fk_kpi_mind_parent')
                    ->references('id')->on('kpi_master_indikators')
                    ->onDelete('cascade');
            });
        }

        // 4. Tabel Pelaksanaan KPI Unit & Cascading Scorecard
        if (!Schema::hasTable('kpi_unit_indikators')) {
            Schema::create('kpi_unit_indikators', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('periode_id');
                $table->char('master_unit_id', 36)->nullable(); // Unit kerja (WR 1, WR 2, BAUK, SDM, dll.)
                $table->char('pegawai_id', 36)->nullable(); // PIC / Pegawai penanggung jawab
                $table->unsignedBigInteger('master_indikator_id')->nullable();
                $table->unsignedBigInteger('parent_unit_indikator_id')->nullable(); // Pohon Cascading dari indikator pimpinan
                $table->enum('jenis_cascading', ['Direct', 'Contribution', 'Enabler'])->default('Direct');

                // Target & Bobot Tahun Berjalan
                $table->decimal('target_angka', 15, 2)->nullable();
                $table->string('target_label', 100)->nullable();
                $table->string('satuan', 50)->nullable();
                $table->decimal('bobot', 5, 2)->default(0);

                // Roadmap Multi-Tahun (Renstra / WR 3)
                $table->string('target_2026', 100)->nullable();
                $table->string('target_2027', 100)->nullable();
                $table->string('target_2028', 100)->nullable();
                $table->string('target_2029', 100)->nullable();

                // Metadata Pendukung
                $table->string('keterkaitan_iku', 255)->nullable();
                $table->string('sumber_data', 255)->nullable();
                $table->string('pic_data', 255)->nullable();
                $table->string('unit_terkait', 255)->nullable();

                // Evaluasi / Realisasi & Skor
                $table->decimal('realisasi_angka', 15, 2)->nullable();
                $table->string('realisasi_label', 100)->nullable();
                $table->decimal('capaian_persen', 8, 2)->nullable();
                $table->decimal('skor', 8, 2)->nullable();
                $table->text('analisis_capaian')->nullable();
                $table->text('kendala')->nullable();
                $table->text('rencana_tindak_lanjut')->nullable();
                $table->string('file_bukti', 255)->nullable();
                $table->enum('status_monev', ['Belum Mengisi', 'Draft', 'Terevaluasi', 'Tercapai', 'Tidak Tercapai'])->default('Belum Mengisi')->index();

                $table->integer('urutan')->default(1);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('periode_id', 'fk_kpi_uind_per')
                    ->references('id')->on('kpi_periodes')
                    ->onDelete('cascade');

                $table->foreign('master_unit_id', 'fk_kpi_uind_unit')
                    ->references('id')->on('master_units')
                    ->onDelete('set null');

                $table->foreign('pegawai_id', 'fk_kpi_uind_peg')
                    ->references('id')->on('data_dosen_tendiks')
                    ->onDelete('set null');

                $table->foreign('master_indikator_id', 'fk_kpi_uind_mind')
                    ->references('id')->on('kpi_master_indikators')
                    ->onDelete('cascade');

                $table->foreign('parent_unit_indikator_id', 'fk_kpi_uind_parent')
                    ->references('id')->on('kpi_unit_indikators')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_unit_indikators');
        Schema::dropIfExists('kpi_master_indikators');
        Schema::dropIfExists('kpi_master_perspektifs');
        Schema::dropIfExists('kpi_periodes');
    }
};
