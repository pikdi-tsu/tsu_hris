<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSyncSecretHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hris:hash-secret {secret? : Plain secret to hash (jika kosong, membaca dari .env HRIS)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SHA-256 hash dari HRIS Secret untuk digunakan pada .env proyek-proyek lain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $secret = $this->argument('secret') ?: (env('HRIS_SYNC_SECRET') ?: config('app.pikdi.key.sync'));

        if (empty($secret)) {
            $this->error('Secret kosong! Silakan isi HRIS_SYNC_SECRET di .env HRIS atau berikan argumen: php artisan hris:hash-secret <secret>');
            return 1;
        }

        $sha256 = hash('sha256', $secret);

        $this->info('===============================================================');
        $this->info('           TSU HRIS SYNC SECRET HASH GENERATOR                 ');
        $this->info('===============================================================');
        $this->line("Plain Secret (HRIS)       : <comment>{$secret}</comment>");
        $this->line("SHA-256 Hash (Proyek Lain): <info>{$sha256}</info>");
        $this->info('---------------------------------------------------------------');
        $this->line('Salin baris berikut ke file <comment>.env</comment> proyek downstream (misal TSU Template/SIMAK/PMB):');
        $this->line("<question>HRIS_SYNC_SECRET={$sha256}</question>");
        $this->info('===============================================================');

        return 0;
    }
}
