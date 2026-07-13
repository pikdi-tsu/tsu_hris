<?php

namespace App\Jobs;

use App\Exports\RiwayatLemburExport;
use App\Models\User;
use App\Notifications\ExportSelesaiNotification;
use App\Notifications\ExportGagalNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ExportRiwayatLemburJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $fileName;

    public function __construct($userId, $fileName)
    {
        $this->userId = $userId;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        $filePath = 'exports/' . $this->fileName;
        
        // Simpan file Excel ke storage public
        Excel::store(new RiwayatLemburExport(), $filePath, 'public');

        // Cari user yang meminta export
        $user = User::find($this->userId);
        if ($user) {
            $downloadUrl = asset('storage/' . $filePath);
            $message = 'File Excel Laporan Riwayat Lembur sudah siap diunduh!';
            
            // Kirim notifikasi Reverb (ShouldBroadcastNow)
            $user->notify(new ExportSelesaiNotification($message, $downloadUrl));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Cari user yang meminta export
        $user = User::find($this->userId);
        if ($user) {
            $message = 'Export Gagal! Terjadi kesalahan sistem saat memproses Laporan Riwayat Lembur.';
            
            // Kirim notifikasi Reverb (ShouldBroadcastNow)
            $user->notify(new ExportGagalNotification($message, $exception->getMessage()));
        }
    }
}
