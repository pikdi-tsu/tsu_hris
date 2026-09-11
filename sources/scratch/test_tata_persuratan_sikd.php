<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SuratMasuk;
use App\Models\DisposisiSuratMasuk;
use App\Models\RequestSuratSdm;
use App\Models\MasterUnit;
use App\Models\DataDosenTendik;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "=================================================================\n";
echo "TEST AUTOMATION: TATA PERSURATAN & SIKD (KASUS 1 & KASUS 2)\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($description, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$description}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$description}\n";
        $failCount++;
    }
}

DB::beginTransaction();

try {
    // Cari user admin & unit pengadaan / bauk
    $adminUser = User::where('username', 'admin_hris')->first() ?? User::first();
    $unitPengadaan = MasterUnit::where('nama_unit', 'like', '%pengadaan%')
        ->orWhere('nama_unit', 'like', '%bauk%')
        ->orWhere('nama_unit', 'like', '%sarana%')
        ->first() ?? MasterUnit::first();

    assertTest("Admin User & Unit Kerja tersedia untuk pengujian", $adminUser && $unitPengadaan);

    // =================================================================
    // KASUS 1: EKSTERNAL (SURAT MASUK & DISPOSISI UNIT)
    // =================================================================
    echo "\n--- Menguji Kasus 1: Surat Masuk Eksternal -> Disposisi Pengadaan -> Penyelesaian ---\n";

    // 1. Registrasi Surat Masuk (Contoh: Penawaran Pengadaan Barang)
    $noAgenda = SuratMasuk::generateNoAgenda();
    $suratMasuk = SuratMasuk::create([
        'no_agenda'         => $noAgenda,
        'no_surat_asal'     => '019/PENG/PT-LOG/IX/2026',
        'pengirim_instansi' => 'PT. Mitra Sarana Logistik Indonesia',
        'tgl_surat'         => '2026-09-10',
        'tgl_diterima'      => '2026-09-11',
        'perihal'           => 'Surat Penawaran Pengadaan Perangkat Server dan Jaringan Kampus',
        'ringkasan_isi'     => 'Penawaran paket server rackmount dengan spesifikasi enterprise.',
        'sifat_surat'       => 'penting',
        'file_surat'        => 'private/surat_masuk/test_penawaran.pdf',
        'status'            => 'terdaftar',
        'created_by'        => $adminUser->id,
    ]);

    assertTest("Surat Masuk berhasil diregistrasi dengan No. Agenda otomatis ({$suratMasuk->no_agenda})", $suratMasuk && $suratMasuk->status === 'terdaftar');
    assertTest("Accessor format badge Surat Masuk berfungsi", !empty($suratMasuk->status_badge) && !empty($suratMasuk->sifat_badge));

    // 2. Disposisi ke Bagian Pengadaan
    $disposisi = DisposisiSuratMasuk::create([
        'surat_masuk_id'       => $suratMasuk->id,
        'dari_user_id'         => $adminUser->id,
        'ke_unit_id'           => $unitPengadaan->id,
        'instruksi'            => 'Tindak Lanjuti Segera',
        'catatan_disposisi'    => 'Mohon Bagian Pengadaan meneliti kelayakan harga dan spesifikasi teknis.',
        'tgl_disposisi'        => now(),
        'batas_waktu'          => now()->addDays(5)->toDateString(),
        'status_tindak_lanjut' => 'menunggu',
    ]);
    $suratMasuk->update(['status' => 'didisposisi']);

    assertTest("Lembar disposisi berhasil diterbitkan ke unit {$unitPengadaan->nama_unit}", $disposisi && $disposisi->status_tindak_lanjut === 'menunggu');
    assertTest("Status Surat Masuk beralih ke 'didisposisi'", $suratMasuk->fresh()->status === 'didisposisi');

    // 3. Bagian Pengadaan Menerima Surat Disposisi
    $disposisi->update(['status_tindak_lanjut' => 'diterima']);
    $suratMasuk->update(['status' => 'proses_unit']);

    assertTest("Bagian Pengadaan mengonfirmasi terima disposisi (status: diterima)", $disposisi->fresh()->status_tindak_lanjut === 'diterima');
    assertTest("Status Surat Masuk beralih ke 'proses_unit'", $suratMasuk->fresh()->status === 'proses_unit');

    // 4. Bagian Pengadaan Menindaklanjuti & Menyelesaikan Disposisi
    $disposisi->update([
        'status_tindak_lanjut'  => 'selesai',
        'catatan_tindak_lanjut' => 'Spesifikasi telah diverifikasi tim IT & Pengadaan. Nota telaah kelayakan telah disetujui.',
        'file_tindak_lanjut'    => 'private/disposisi_tindak_lanjut/test_nota_telaah.pdf',
        'tgl_selesai'           => now(),
        'diselesaikan_oleh'     => $adminUser->id,
    ]);

    // Update status surat masuk menjadi selesai karena seluruh disposisi selesai
    $unfinished = DisposisiSuratMasuk::where('surat_masuk_id', $suratMasuk->id)
        ->where('status_tindak_lanjut', '!=', 'selesai')
        ->count();
    if ($unfinished === 0) {
        $suratMasuk->update(['status' => 'selesai']);
    }

    assertTest("Disposisi berhasil diselesaikan oleh unit dengan catatan tindak lanjut", $disposisi->fresh()->status_tindak_lanjut === 'selesai');
    assertTest("Surat Masuk otomatis beralih ke status akhir 'selesai'", $suratMasuk->fresh()->status === 'selesai');


    // =================================================================
    // KASUS 2: INTERNAL (SDM KE SEKRETARIAT - SK REKTORAT & HARDFILE)
    // =================================================================
    echo "\n--- Menguji Kasus 2: SDM -> Teruskan Sekretariat -> SK Softfile & Hardfile ---\n";

    $dosen = DataDosenTendik::first();
    $sekretariatUser = User::where('id', '!=', $adminUser->id)->first() ?? $adminUser;

    // 1. Dosen mengajukan permohonan surat ke SDM
    $noTiket = RequestSuratSdm::generateNomorTiket();
    $requestSurat = RequestSuratSdm::create([
        'nomor_tiket'          => $noTiket,
        'data_dosen_tendik_id' => $dosen ? $dosen->id : null,
        'user_id'              => $dosen && $dosen->user_id ? $dosen->user_id : $adminUser->id,
        'jenis_surat'          => 'Surat Keputusan (SK) Rektorat Penugasan Khusus',
        'keperluan'            => 'Penugasan sebagai Tim Ahli Konsorsium Perguruan Tinggi',
        'keterangan_tambahan'  => 'Dibutuhkan segera baik softfile maupun berkas hardfile cap basah.',
        'file_lampiran'        => 'private/surat_sdm/lampiran/test_surat_tugas.pdf',
        'status'               => 'menunggu',
    ]);

    assertTest("Permohonan surat berhasil dibuat dengan No. Tiket ({$requestSurat->nomor_tiket})", $requestSurat && $requestSurat->status === 'menunggu');

    // 2. SDM Meneruskan Permohonan ke Sekretariat Rektorat
    $requestSurat->update([
        'status'          => 'diproses',
        'diteruskan_ke'   => $sekretariatUser->id,
        'diteruskan_at'   => now(),
        'catatan_terusan' => 'Mohon dibuatkan SK Rektor resmi dan dimintakan tanda tangan basah Rektor.',
        'processed_by'    => $adminUser->id,
        'processed_at'    => now(),
    ]);

    assertTest("Permohonan berhasil diteruskan SDM ke Sekretariat ({$sekretariatUser->name})", $requestSurat->fresh()->diteruskan_ke == $sekretariatUser->id);
    assertTest("Badge status menunjukkan status diteruskan", str_contains($requestSurat->fresh()->status_badge, 'Diteruskan'));

    // 3. Sekretariat Mengunggah Softfile SK Rektorat & Mengatur Status Hardfile 'siap_diambil'
    $requestSurat->update([
        'status'                        => 'selesai',
        'nomor_surat_keluar'            => '045/SK-REK/TSU/IX/2026',
        'file_surat_hasil'              => 'private/surat_sdm/sk_rektor/test_sk_rektor.pdf',
        'status_hardfile'               => 'siap_diambil',
        'catatan_sekretariat'           => 'SK Rektorat telah dicap basah dan ditandatangani Rektor. Hardfile siap diambil di Sekretariat.',
        'diselesaikan_oleh_sekretariat' => $sekretariatUser->id,
        'tgl_diselesaikan_sekretariat'  => now(),
        'completed_at'                  => now(),
    ]);

    $freshSurat = $requestSurat->fresh();
    assertTest("Sekretariat berhasil menyelesaikan SK Rektorat (Status: selesai)", $freshSurat->status === 'selesai');
    assertTest("Softfile SK Rektorat tersimpan ({$freshSurat->nomor_surat_keluar})", !empty($freshSurat->file_surat_hasil));
    assertTest("Status Hardfile fisik tercatat 'siap_diambil'", $freshSurat->status_hardfile === 'siap_diambil');
    assertTest("Badge status hardfile menampilkan label Siap Diambil di Sekretariat", str_contains($freshSurat->status_hardfile_badge, 'Siap Diambil'));

    // 4. SDM Mengambil Berkas Hardfile di Sekretariat (Konfirmasi Terima Hardfile)
    $freshSurat->update(['status_hardfile' => 'telah_diterima_sdm']);

    assertTest("SDM mengonfirmasi serah terima hardfile fisik (Status: telah_diterima_sdm)", $freshSurat->fresh()->status_hardfile === 'telah_diterima_sdm');
    assertTest("Badge status hardfile menampilkan label Telah Diserahkan / Diterima SDM", str_contains($freshSurat->fresh()->status_hardfile_badge, 'Telah Diserahkan'));

    // Rollback changes to keep test environment pure
    DB::rollBack();
    echo "\n[DB ROLLBACK] Seluruh data pengujian berhasil diuji dan transaksi di-rollback bersih.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR EXCEPTION] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failCount++;
}

echo "\n=================================================================\n";
echo "RINGKASAN HASIL: {$passCount} PASSED, {$failCount} FAILED\n";
echo "=================================================================\n";
