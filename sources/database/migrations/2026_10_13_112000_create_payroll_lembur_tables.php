<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableKaryawan = 'data_dosen_tendiks';

        // 1. Tabel Periode Payroll Lembur
        Schema::create('payroll_lembur_periods', function (Blueprint $table) use ($tableKaryawan) {
            $table->uuid('id')->primary();
            $table->string('nama_periode', 150); // Contoh: Lembur Tendik TSU - Februari 2026
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun'); // 2026
            $table->date('start_date_cutoff'); // Tanggal mulai cut-off lembur
            $table->date('end_date_cutoff'); // Tanggal selesai cut-off lembur
            $table->date('tanggal_surat')->nullable(); // Tanggal di slip PDF (misal 25 Februari 2026)
            $table->string('kota_surat', 100)->default('Surakarta');
            $table->string('penandatangan_nama', 150)->default('Afifah Raisya Putri Sanjaya, S.I.P.');
            $table->string('penandatangan_jabatan', 150)->default('Bagian SDM');
            $table->string('status', 50)->default('draft'); // draft, pending_approval, approved, locked
            $table->unsignedInteger('total_pegawai')->default(0);
            $table->decimal('total_jam_lembur', 10, 2)->default(0);
            $table->decimal('total_anggaran_lembur', 15, 2)->default(0);

            // Approval Workflow
            $table->foreignUuid('validator_1_id')->nullable()->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('validator_2_id')->nullable()->constrained($tableKaryawan)->onDelete('set null');
            $table->foreignUuid('approval_id')->nullable()->constrained($tableKaryawan)->onDelete('set null');
            $table->string('rejection_by_role', 100)->nullable();
            $table->text('rejection_note')->nullable();
            $table->text('unlocked_reason')->nullable();

            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->uuid('locked_by')->nullable();

            $table->text('keterangan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['bulan', 'tahun', 'status'], 'idx_plp_periode_status');
        });

        // 2. Tabel Rincian Pegawai Lembur (Rekapitulasi per Karyawan)
        Schema::create('payroll_lembur_karyawans', function (Blueprint $table) use ($tableKaryawan) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_lembur_period_id')->constrained('payroll_lembur_periods')->cascadeOnDelete();
            $table->uuid('data_dosen_tendik_id')->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('nama', 150);
            $table->string('tipe_karyawan', 50)->default('Tendik');
            $table->string('posisi', 150)->nullable();
            $table->string('nama_unit', 150)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('atas_nama_rekening', 150)->nullable();

            // Formula Dasar
            $table->decimal('gaji_tetap', 15, 2)->default(0);
            $table->decimal('upah_per_jam', 15, 2)->default(0); // Gaji Tetap / 173

            // Statistik Jam & Hari
            $table->unsignedSmallInteger('total_hari_lembur')->default(0);
            $table->decimal('total_jam_lembur', 10, 2)->default(0);
            $table->decimal('jam_kerja_biasa', 10, 2)->default(0);
            $table->decimal('jam_hari_libur', 10, 2)->default(0);

            // Rupiah
            $table->decimal('nominal_lembur_sistem', 15, 2)->default(0);
            $table->decimal('penyesuaian', 15, 2)->default(0); // Koreksi manual
            $table->decimal('total_lembur_final', 15, 2)->default(0); // nominal_lembur_sistem + penyesuaian
            $table->text('catatan_koreksi')->nullable();
            $table->timestamps();

            $table->foreign('data_dosen_tendik_id')->references('id')->on($tableKaryawan)->nullOnDelete();
            $table->index(['payroll_lembur_period_id', 'data_dosen_tendik_id'], 'idx_plk_period_dosen');
        });

        // 3. Tabel Detail Setiap Hari Lembur Karyawan (Untuk Tampilan Tabel di Slip PDF)
        Schema::create('payroll_lembur_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_lembur_karyawan_id')->constrained('payroll_lembur_karyawans')->cascadeOnDelete();
            $table->unsignedBigInteger('lembur_karyawan_id')->nullable(); // ID dari lembur_karyawans
            $table->date('tanggal'); // Tanggal lembur
            $table->string('hari_tanggal_label', 100); // Contoh: Selasa, 20 Januari 2026
            $table->string('jenis_lembur', 50)->default('Hari Kerja'); // 'Hari Kerja' / 'Hari Libur'
            $table->decimal('jam_lembur', 8, 2)->default(0); // Misal 5.00 jam
            $table->decimal('upah_per_jam', 15, 2)->default(0); // 28.902
            $table->decimal('koefisien', 8, 2)->default(0); // Pengali (misal 9.50)
            $table->decimal('total_nominal', 15, 2)->default(0); // 274.566
            $table->text('keterangan_pekerjaan')->nullable();
            $table->timestamps();

            $table->index(['payroll_lembur_karyawan_id', 'tanggal'], 'idx_pld_karyawan_tgl');
        });

        // 4. Tabel Riwayat Approval Payroll Lembur
        Schema::create('payroll_lembur_period_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_lembur_period_id')->constrained('payroll_lembur_periods')->cascadeOnDelete();
            $table->string('step', 50); // draft, validator_1, validator_2, approval, unlocked
            $table->string('role_label', 100);
            $table->uuid('user_id')->nullable();
            $table->uuid('karyawan_id')->nullable();
            $table->string('karyawan_name', 150);
            $table->string('action', 50); // submitted, approved, revision_requested, unlocked
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 5. Daftarkan Hak Akses / Permission
        $permissions = [
            'admin:payroll-lembur:view',
            'admin:payroll-lembur:create',
            'admin:payroll-lembur:edit',
            'admin:payroll-lembur:delete',
            'admin:payroll-lembur:approve',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Assign ke Super Admin HRIS dan Role Admin
        $superAdminRole = Role::where('name', 'super admin hris')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        $rolesWithPayroll = Role::whereHas('permissions', function ($q) {
            $q->where('name', 'admin:payroll:view');
        })->get();

        foreach ($rolesWithPayroll as $role) {
            $role->givePermissionTo($permissions);
        }

        // 6. Daftarkan Menu Sidebar ke Tabel system_menu_sidebars
        // Parent: PAYROLL (ID: 30)
        $parentMenu = DB::table('system_menu_sidebars')->where('id', 30)->first();
        $parentId = $parentMenu ? 30 : null;

        $exists = DB::table('system_menu_sidebars')->where('route', 'admin.payroll.lembur.index')->exists();
        if (!$exists) {
            $maxOrder = DB::table('system_menu_sidebars')->where('parent_id', $parentId)->max('order') ?? 3;
            DB::table('system_menu_sidebars')->insert([
                'name'            => 'Payroll Lembur',
                'icon'            => 'fas fa-business-time',
                'type'            => 'item',
                'route'           => 'admin.payroll.lembur.index',
                'permission_name' => 'admin:payroll-lembur:view',
                'parent_id'       => $parentId,
                'order'           => $maxOrder + 1,
                'isactive'        => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_menu_sidebars')->where('route', 'admin.payroll.lembur.index')->delete();

        Schema::dropIfExists('payroll_lembur_period_approvals');
        Schema::dropIfExists('payroll_lembur_details');
        Schema::dropIfExists('payroll_lembur_karyawans');
        Schema::dropIfExists('payroll_lembur_periods');

        $permissions = [
            'admin:payroll-lembur:view',
            'admin:payroll-lembur:create',
            'admin:payroll-lembur:edit',
            'admin:payroll-lembur:delete',
            'admin:payroll-lembur:approve',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
