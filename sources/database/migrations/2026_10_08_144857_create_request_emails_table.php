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
        if (!Schema::hasTable('request_emails')) {
            Schema::create('request_emails', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nomor_tiket', 50)->unique(); // REQ-MAIL-2026-001
                $table->uuid('pegawai_id')->nullable();
                $table->string('nama_pemohon', 150);
                $table->string('nik_nip', 50)->nullable();
                $table->uuid('unit_id')->nullable();
                $table->string('unit_nama', 150)->nullable();
                $table->string('jabatan', 150)->nullable();
                $table->enum('tipe_pemohon', ['Dosen', 'Tendik', 'Unit Kerja', 'Organisasi / Kepanitiaan'])->default('Tendik');
                $table->string('keperluan', 255);
                $table->string('email_usulan', 150); // Usulan awal dari pemohon, misal: afifah@tsu.ac.id
                $table->string('email_rekomendasi_sdm', 150)->nullable(); // SDM bisa edit/koreksi sesuai standar penamaan resmi
                $table->string('email_final_dibuat', 150)->nullable(); // Alamat yang akhirnya diterbitkan oleh PIKDI
                $table->string('kontak_hp', 30);
                $table->string('email_alternatif', 150)->nullable();
                $table->enum('status', [
                    'Diajukan',          // Menunggu validasi SDM
                    'Divalidasi SDM',    // Divalidasi SDM, menunggu eksekusi PIKDI
                    'Ditolak SDM',       // Ditolak oleh SDM
                    'Diproses PIKDI',    // Sedang dibuat akun oleh PIKDI
                    'Selesai',           // Akun email aktif & kredensial diserahkan
                    'Ditolak PIKDI'      // Ditolak oleh PIKDI karena kendala teknis
                ])->default('Diajukan');
                $table->text('catatan_pemohon')->nullable();
                $table->text('catatan_sdm')->nullable();
                $table->text('catatan_pikdi')->nullable();
                $table->string('password_sementara', 100)->nullable();
                $table->uuid('validated_by')->nullable(); // User SDM
                $table->timestamp('validated_at')->nullable();
                $table->uuid('processed_by')->nullable(); // User PIKDI
                $table->timestamp('processed_at')->nullable();
                $table->json('riwayat_status')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_emails');
    }
};
