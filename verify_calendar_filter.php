<?php
require 'sources/vendor/autoload.php';
$app = require_once 'sources/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create 3 test events:
// A: Target Semua Pegawai
// B: Target Dosen Saja
// C: Target Tendik Saja
$today = date('Y-m-d');
$kegiatanSemua = App\Models\Kegiatan::create([
    'nama_kegiatan'    => 'UJI KALENDER: Rapat Kerja Umum',
    'kategori'         => 'Rapat Dinas',
    'tanggal_kegiatan' => $today,
    'jam_mulai'        => '09:00:00',
    'jam_selesai'      => '11:00:00',
    'lokasi'           => 'Auditorium Kampus',
    'target_peserta'   => 'Semua Pegawai',
    'status'           => 'Dibuka',
]);

$kegiatanDosen = App\Models\Kegiatan::create([
    'nama_kegiatan'    => 'UJI KALENDER: Workshop Penulisan Jurnal Dosen',
    'kategori'         => 'Seminar/Workshop',
    'tanggal_kegiatan' => $today,
    'jam_mulai'        => '13:00:00',
    'jam_selesai'      => '15:00:00',
    'lokasi'           => 'Lab Komputer',
    'target_peserta'   => 'Dosen Saja',
    'status'           => 'Dibuka',
]);

$kegiatanTendik = App\Models\Kegiatan::create([
    'nama_kegiatan'    => 'UJI KALENDER: Pelatihan Arsip Digital Tendik',
    'kategori'         => 'Seminar/Workshop',
    'tanggal_kegiatan' => $today,
    'jam_mulai'        => '15:30:00',
    'jam_selesai'      => '17:00:00',
    'lokasi'           => 'Ruang Meeting Biro',
    'target_peserta'   => 'Tendik Saja',
    'status'           => 'Dibuka',
]);

echo "Created 3 test events on {$today}\n";

$controller = new Modules\Users\Http\Controllers\SelfService\DashboardController();

// Test A: Superadmin login -> should see all 3 events
session(['is_superadmin' => true]);
$admin = App\Models\User::first();
Illuminate\Support\Facades\Auth::login($admin);

$req = new Illuminate\Http\Request([
    'start' => date('Y-m-01'),
    'end'   => date('Y-m-t'),
]);

$response = $controller->getHolidays($req);
$events = json_decode($response->getContent(), true);

$kegiatanTitles = [];
foreach ($events as $ev) {
    if (isset($ev['extendedProps']['type']) && $ev['extendedProps']['type'] === 'kegiatan') {
        $kegiatanTitles[] = $ev['title'];
    }
}

echo "\n--- TEST A: Superadmin Perspective ---\n";
print_r($kegiatanTitles);
if (count($kegiatanTitles) >= 3) {
    echo "✓ Superadmin sees all target audiences correctly!\n";
} else {
    echo "✗ Expected at least 3 events for superadmin, got " . count($kegiatanTitles) . "\n";
}

// Test B: Regular User login (e.g. Tendik)
session(['is_superadmin' => false]);
$tendikUser = App\Models\User::whereHas('karyawan', function($q) {
    $q->where('jenis_pegawai', 'Tendik');
})->first();

if ($tendikUser) {
    Illuminate\Support\Facades\Auth::login($tendikUser);
    $responseTendik = $controller->getHolidays($req);
    $eventsTendik = json_decode($responseTendik->getContent(), true);

    $tendikTitles = [];
    foreach ($eventsTendik as $ev) {
        if (isset($ev['extendedProps']['type']) && $ev['extendedProps']['type'] === 'kegiatan') {
            $tendikTitles[] = $ev['title'];
        }
    }

    echo "\n--- TEST B: Tendik User Perspective ({$tendikUser->name}) ---\n";
    print_r($tendikTitles);
    $hasDosen = false;
    $hasTendik = false;
    $hasSemua = false;
    foreach ($tendikTitles as $t) {
        if (str_contains($t, 'Dosen Saja') || str_contains($t, 'Jurnal Dosen')) $hasDosen = true;
        if (str_contains($t, 'Tendik Saja') || str_contains($t, 'Arsip Digital')) $hasTendik = true;
        if (str_contains($t, 'Rapat Kerja Umum')) $hasSemua = true;
    }

    if ($hasSemua && $hasTendik && !$hasDosen) {
        echo "✓ Tendik correctly sees 'Semua Pegawai' and 'Tendik Saja', and DOES NOT see 'Dosen Saja'!\n";
    } else {
        echo "Note: Tendik filter evaluation: Semua={$hasSemua}, Tendik={$hasTendik}, Dosen={$hasDosen}\n";
    }
}

// Clean up test events
App\Models\Kegiatan::where('nama_kegiatan', 'like', 'UJI KALENDER%')->delete();
echo "\nCleaned up test events.\n";
unlink(__FILE__);
