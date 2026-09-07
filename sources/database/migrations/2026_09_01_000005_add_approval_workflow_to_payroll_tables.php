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

        // 1. Update payroll_periods table
        Schema::table('payroll_periods', function (Blueprint $table) use ($tableKaryawan) {
            $table->foreignUuid('validator_1_id')->nullable()->after('end_date_cutoff')->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('validator_2_id')->nullable()->after('validator_1_id')->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('approval_id')->nullable()->after('validator_2_id')->constrained($tableKaryawan)->onDelete('set null');

            // Ubah kolom status menjadi string agar fleksibel mendukung berbagai tingkatan status approval
            $table->string('status', 50)->default('draft')->change();

            $table->string('rejection_by_role', 100)->nullable()->after('status');
            $table->text('rejection_note')->nullable()->after('rejection_by_role');
            $table->text('unlocked_reason')->nullable()->after('rejection_note');
        });

        // 2. Buat tabel riwayat approval payroll_period_approvals
        Schema::create('payroll_period_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
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
        Schema::dropIfExists('payroll_period_approvals');

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['validator_1_id']);
            $table->dropForeign(['validator_2_id']);
            $table->dropForeign(['approval_id']);
            $table->dropColumn(['validator_1_id', 'validator_2_id', 'approval_id', 'rejection_by_role', 'rejection_note', 'unlocked_reason']);
        });
    }
};
