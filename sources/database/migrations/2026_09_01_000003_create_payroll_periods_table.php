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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_periode', 50)->unique();
            $table->string('nama_periode', 100);
            $table->enum('tipe', ['karyawan', 'honorarium'])->default('karyawan');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->date('start_date_cutoff')->nullable();
            $table->date('end_date_cutoff')->nullable();
            $table->enum('status', ['draft', 'locked'])->default('draft');
            $table->timestamp('locked_at')->nullable();
            $table->uuid('locked_by')->nullable();
            $table->unsignedInteger('total_pegawai')->default(0);
            $table->decimal('total_gaji_kotor', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('total_gaji_bersih', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
