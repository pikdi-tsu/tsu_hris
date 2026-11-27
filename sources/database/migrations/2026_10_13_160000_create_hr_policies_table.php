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
        if (!Schema::hasTable('hr_policies')) {
            Schema::create('hr_policies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kategori', 50)->index(); // renstra, peraturan, sop, tupoksi
                $table->string('judul', 255);
                $table->string('file_path', 255);
                $table->string('file_name', 255)->nullable();
                $table->unsignedBigInteger('file_size')->nullable(); // Dalam bytes
                $table->text('keterangan')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_policies');
    }
};
