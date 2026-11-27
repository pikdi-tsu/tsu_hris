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
        // 1. Buat Tabel master_aspek_surveys
        if (!Schema::hasTable('master_aspek_surveys')) {
            Schema::create('master_aspek_surveys', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->enum('kategori', ['training', 'layanan_sdm'])->index();
                $table->string('kode', 50)->nullable()->index();
                $table->string('nama_aspek');
                $table->text('deskripsi')->nullable();
                $table->integer('urutan')->default(1);
                $table->boolean('is_active')->default(true)->index();
                $table->integer('skala_min')->default(1);
                $table->integer('skala_max')->default(5);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambah kolom jawaban_detail (JSON) ke tabel response jika belum ada
        if (Schema::hasTable('survey_pelatihan_responses') && !Schema::hasColumn('survey_pelatihan_responses', 'jawaban_detail')) {
            Schema::table('survey_pelatihan_responses', function (Blueprint $table) {
                $table->json('jawaban_detail')->nullable()->after('feedback_manfaat');
            });
        }

        if (Schema::hasTable('survey_layanan_responses') && !Schema::hasColumn('survey_layanan_responses', 'jawaban_detail')) {
            Schema::table('survey_layanan_responses', function (Blueprint $table) {
                $table->json('jawaban_detail')->nullable()->after('kritik_saran');
            });
        }

        // 3. Tambah Permissions Spatie
        $permissions = [
            'admin:master-aspek-survey:view',
            'admin:master-aspek-survey:create',
            'admin:master-aspek-survey:edit',
            'admin:master-aspek-survey:delete',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        $superAdmin = Role::where('name', 'super admin hris')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // 4. Tambah Menu Sidebar di bawah Master Data (parent_id: 7)
        $maxOrder = DB::table('system_menu_sidebars')->where('parent_id', 7)->max('order') ?? 20;

        DB::table('system_menu_sidebars')->updateOrInsert(
            ['route' => 'admin.master-aspek-survey.index'],
            [
                'name'            => 'Aspek Survei Kepuasan',
                'icon'            => 'fas fa-poll-h',
                'type'            => 'item',
                'route'           => 'admin.master-aspek-survey.index',
                'permission_name' => 'admin:master-aspek-survey:view',
                'parent_id'       => 7, // Submenu dari "Master Data"
                'order'           => $maxOrder + 1,
                'isactive'        => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        // 5. Seed data aspek awal (Training & Layanan SDM)
        $initialData = [
            // Training
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'training',
                'kode'        => 'skor_relevansi',
                'nama_aspek'  => 'Relevansi materi dengan kebutuhan pekerjaan',
                'deskripsi'   => 'Kesesuaian kurikulum dan materi pelatihan terhadap tugas kedinasan sehari-hari.',
                'urutan'      => 1,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'training',
                'kode'        => 'skor_narasumber',
                'nama_aspek'  => 'Kualitas penyampaian narasumber',
                'deskripsi'   => 'Penguasaan materi, kejelasan pemaparan, dan interaktivitas fasilitator.',
                'urutan'      => 2,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'training',
                'kode'        => 'skor_metode',
                'nama_aspek'  => 'Media dan metode pelatihan',
                'deskripsi'   => 'Efektivitas modul, studi kasus, presentasi, dan metode pembelajaran.',
                'urutan'      => 3,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'training',
                'kode'        => 'skor_fasilitas',
                'nama_aspek'  => 'Fasilitas dan sarana prasarana',
                'deskripsi'   => 'Kenyamanan ruangan, audio-visual, konsumsi, dan sarana pendukung.',
                'urutan'      => 4,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'training',
                'kode'        => 'skor_manfaat',
                'nama_aspek'  => 'Manfaat pelatihan bagi pengembangan diri',
                'deskripsi'   => 'Dampak peningkatan kompetensi teknis dan profesionalisme peserta.',
                'urutan'      => 5,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // Layanan SDM
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'layanan_sdm',
                'kode'        => 'skor_responsiveness',
                'nama_aspek'  => 'Ketanggapan & Responsivitas Layanan SDM',
                'deskripsi'   => 'Kecepatan petugas SDM dalam merespon permohonan, pertanyaan, dan konsultasi pegawai.',
                'urutan'      => 1,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'layanan_sdm',
                'kode'        => 'skor_keramahan',
                'nama_aspek'  => 'Keramahan & Kesopanan Staf SDM',
                'deskripsi'   => 'Sikap santun, komunikatif, dan solutif dalam melayani civitas akademika.',
                'urutan'      => 2,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'layanan_sdm',
                'kode'        => 'skor_kecepatan',
                'nama_aspek'  => 'Kecepatan & Ketepatan Proses Layanan',
                'deskripsi'   => 'Dokumen dan permohonan (surat, cuti, izin, lembur, SK) diproses tepat waktu sesuai SOP.',
                'urutan'      => 3,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'kategori'    => 'layanan_sdm',
                'kode'        => 'skor_fasilitas',
                'nama_aspek'  => 'Fasilitas & Kemudahan Akses HRIS',
                'deskripsi'   => 'Keandalan, kemudahan penggunaan, dan ketersediaan sistem informasi SDM online.',
                'urutan'      => 4,
                'is_active'   => true,
                'skala_min'   => 1,
                'skala_max'   => 5,
                'created_by'  => 'System Seeder',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($initialData as $row) {
            DB::table('master_aspek_surveys')->updateOrInsert(
                ['kategori' => $row['kategori'], 'kode' => $row['kode']],
                $row
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_menu_sidebars')->where('route', 'admin.master-aspek-survey.index')->delete();

        $permissions = [
            'admin:master-aspek-survey:view',
            'admin:master-aspek-survey:create',
            'admin:master-aspek-survey:edit',
            'admin:master-aspek-survey:delete',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->first();
            if ($p) {
                $p->delete();
            }
        }

        if (Schema::hasColumn('survey_pelatihan_responses', 'jawaban_detail')) {
            Schema::table('survey_pelatihan_responses', function (Blueprint $table) {
                $table->dropColumn('jawaban_detail');
            });
        }

        if (Schema::hasColumn('survey_layanan_responses', 'jawaban_detail')) {
            Schema::table('survey_layanan_responses', function (Blueprint $table) {
                $table->dropColumn('jawaban_detail');
            });
        }

        Schema::dropIfExists('master_aspek_surveys');
    }
};
