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
        Schema::table('manpower_plannings', function (Blueprint $table) {
            $table->uuid('id_pengaju')->nullable()->change();
            $table->uuid('unit_id')->nullable()->change();
            $table->uuid('jabatan_id')->nullable()->change();
            $table->uuid('approved_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpower_plannings', function (Blueprint $table) {
            $table->foreignId('id_pengaju')->nullable()->change();
            $table->foreignId('unit_id')->nullable()->change();
            $table->foreignId('jabatan_id')->nullable()->change();
            $table->foreignId('approved_by')->nullable()->change();
        });
    }
};
