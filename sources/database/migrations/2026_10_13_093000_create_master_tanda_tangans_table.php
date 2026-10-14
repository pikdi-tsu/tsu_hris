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
        if (!Schema::hasTable('master_tanda_tangans')) {
            Schema::create('master_tanda_tangans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('karyawan_id')->nullable();
                $table->string('kode', 50)->nullable();
                $table->string('nama_pejabat');
                $table->string('nip_nik', 50)->nullable();
                $table->string('jabatan');
                $table->string('peran_approval', 50)->nullable()->comment('pembuat_draft, validator_1, validator_2, approval_final, pimpinan');
                $table->string('peruntukan', 50)->default('semua')->comment('semua, slip_gaji, slip_thr, slip_honorarium, surat_sdm');
                $table->string('file_ttd')->nullable();
                $table->string('file_stempel')->nullable();
                $table->text('keterangan')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->integer('urutan')->default(0);
                $table->timestamps();

                $table->foreign('karyawan_id')->references('id')->on('data_dosen_tendiks')->nullOnDelete();
            });
        }

        // 1. Tambah Permissions
        $permissions = [
            'admin:master-tanda-tangan:view',
            'admin:master-tanda-tangan:create',
            'admin:master-tanda-tangan:edit',
            'admin:master-tanda-tangan:delete',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // Assign ke Super Admin HRIS
        $superAdmin = Role::where('name', 'super admin hris')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // 2. Tambah Menu Sidebar di bawah Master Data (parent_id: 7)
        $maxOrder = DB::table('system_menu_sidebars')->where('parent_id', 7)->max('order') ?? 15;

        DB::table('system_menu_sidebars')->updateOrInsert(
            ['route' => 'admin.master-tanda-tangan.index'],
            [
                'name'            => 'Tanda Tangan & Stempel',
                'icon'            => 'fas fa-signature',
                'type'            => 'item',
                'route'           => 'admin.master-tanda-tangan.index',
                'permission_name' => 'admin:master-tanda-tangan:view',
                'parent_id'       => 7, // Submenu dari "Master Data"
                'order'           => $maxOrder + 1,
                'isactive'        => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_menu_sidebars')->where('route', 'admin.master-tanda-tangan.index')->delete();

        $permissions = [
            'admin:master-tanda-tangan:view',
            'admin:master-tanda-tangan:create',
            'admin:master-tanda-tangan:edit',
            'admin:master-tanda-tangan:delete',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->first();
            if ($p) {
                $p->delete();
            }
        }

        Schema::dropIfExists('master_tanda_tangans');
    }
};
