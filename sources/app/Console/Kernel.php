<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Otomatisasi Reset Saldo Cuti Lama (Opsi A: Hangus per 31 Des 23:59)
        $schedule->command('cuti:proses-saldo-tahunan --reset-only')
            ->yearlyOn(12, 31, '23:59')
            ->name('reset-saldo-cuti-tahunan')
            ->withoutOverlapping();

        // Otomatisasi Alokasi Saldo Cuti Baru (12 Hari untuk Masa Kerja >= 2 Thn per 1 Jan 00:01)
        $schedule->command('cuti:proses-saldo-tahunan')
            ->yearlyOn(1, 1, '00:01')
            ->name('generate-saldo-cuti-tahunan')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
