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
        Schema::table('master_units', function (Blueprint $table) {
            $table->uuid('parent_unit_id')->nullable()->after('id');
            $table->foreign('parent_unit_id')->references('id')->on('master_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_units', function (Blueprint $table) {
            $table->dropForeign(['parent_unit_id']);
            $table->dropColumn('parent_unit_id');
        });
    }
};
