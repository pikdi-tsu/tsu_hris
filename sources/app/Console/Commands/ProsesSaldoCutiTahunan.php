<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SaldoCutiService;
use Illuminate\Support\Facades\Log;

class ProsesSaldoCutiTahunan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:proses-saldo-tahunan
                            {--tahun= : Tahun target alokasi saldo cuti (default: tahun berjalan/baru)}
                            {--reset-only : Hanya reset/nonaktifkan saldo tahun lama tanpa generate saldo baru}
                            {--force-all : Timpa saldo jika sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomasi Reset Saldo Cuti Tahun Lama (Opsi A) dan Alokasi Saldo Cuti Tahunan Baru (12 Hari, Syarat Masa Kerja >= 2 Tahun)';

    /**
     * Execute the console command.
     */
    public function handle(SaldoCutiService $service): int
    {
        $targetYear = $this->option('tahun') ? (int)$this->option('tahun') : (int)date('Y');
        $resetOnly = $this->option('reset-only');
        $forceAll = $this->option('force-all');

        $this->info("=================================================");
        $this->info(" PROSES OTOMASI SALDO CUTI TAHUNAN - TSU HRIS   ");
        $this->info(" Tahun Target : {$targetYear}                   ");
        $this->info(" Waktu Eksekusi: " . now()->toDateTimeString());
        $this->info("=================================================");

        try {
            if ($resetOnly) {
                $this->warn("Mode: Hanya Reset/Nonaktifkan Saldo Tahun Lama (< {$targetYear})");
                $updated = \App\Models\SaldoCutiKaryawan::where('tahun', '<', $targetYear)
                    ->where('is_active', '1')
                    ->update([
                        'is_active' => '0',
                        'updated_at' => now(),
                        'updated_by' => 'Artisan Scheduler (Reset-Only)',
                    ]);
                $this->info("Berhasil menonaktifkan {$updated} saldo cuti tahun lama.");
                return Command::SUCCESS;
            }

            $this->info("Menjalankan alokasi saldo cuti (Jatah 12 Hari, Masa Kerja >= 2 Tahun)...");

            $result = $service->generateSaldoTahunan(
                $targetYear,
                true, // Reset previous years (Opsi A)
                !$forceAll, // Only unassigned jika tidak force
                12, // Default 12 hari
                'Artisan Scheduler'
            );

            $this->table(
                ['Parameter', 'Nilai'],
                [
                    ['Tahun Alokasi', $result['tahun']],
                    ['Jatah Standar', $result['default_jatah'] . ' Hari'],
                    ['Total Pegawai Aktif', $result['total_pegawai_aktif']],
                    ['Saldo Baru Dibuat', $result['generated_count']],
                    ['Sudah Memiliki Saldo', $result['already_exists_count']],
                    ['Dilewati (< 2 Thn)', $result['skipped_tenure_count']],
                    ['Saldo Lama Dinonaktifkan', $result['deactivated_old_count']],
                ]
            );

            Log::info("[CRON_SALDO_CUTI] Berhasil memproses saldo cuti tahun {$targetYear}: {$result['generated_count']} dibuat, {$result['skipped_tenure_count']} dilewati (< 2 thn), {$result['deactivated_old_count']} lama dinonaktifkan.");

            $this->info("Proses selesai dengan sukses!");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal memproses saldo cuti: " . $e->getMessage());
            Log::error("[CRON_SALDO_CUTI_ERROR] " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
