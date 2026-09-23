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
        $tableKaryawan = 'data_dosen_tendiks';

        // 1. Update thr_periods table
        Schema::table('thr_periods', function (Blueprint $table) use ($tableKaryawan) {
            $table->foreignUuid('validator_1_id')->nullable()->after('keterangan')->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('validator_2_id')->nullable()->after('validator_1_id')->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('approval_id')->nullable()->after('validator_2_id')->constrained($tableKaryawan)->onDelete('set null');

            $table->string('rejection_by_role', 100)->nullable()->after('status');
            $table->text('rejection_note')->nullable()->after('rejection_by_role');
            $table->text('unlocked_reason')->nullable()->after('rejection_note');
            $table->timestamp('locked_at')->nullable()->after('status');
            $table->uuid('locked_by')->nullable()->after('locked_at');
        });

        // Ensure status column supports multi-level approval stages
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE thr_periods MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");

        // 2. Create thr_period_approvals table
        Schema::create('thr_period_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('thr_period_id')->constrained('thr_periods')->onDelete('cascade');
            $table->string('step', 50); // draft, validator_1, validator_2, approval, unlocked
            $table->string('role_label', 100); // Pembuat Draft, Validator 1, Validator 2, Approval Paling Atas
            $table->uuid('user_id')->nullable();
            $table->uuid('karyawan_id')->nullable();
            $table->string('karyawan_name', 150);
            $table->string('action', 50); // submitted, approved, revision_requested, unlocked
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thr_period_approvals');

        Schema::table('thr_periods', function (Blueprint $table) {
            $table->dropForeign(['validator_1_id']);
            $table->dropForeign(['validator_2_id']);
            $table->dropForeign(['approval_id']);
            $table->dropColumn(['validator_1_id', 'validator_2_id', 'approval_id', 'rejection_by_role', 'rejection_note', 'unlocked_reason']);
        });
    }
};
