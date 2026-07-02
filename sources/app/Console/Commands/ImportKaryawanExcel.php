<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KaryawanImport;

class ImportKaryawanExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:karyawan {file : The path to the Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data Karyawan (Dosen/Tendik) and Auto-create Units from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $this->info("Mulai melakukan import data dari: {$filePath}");
        $this->info("Sistem akan membaca format Dosen/Tendik secara dinamis...");

        try {
            Excel::import(new KaryawanImport, $filePath);
            $this->info("Import berhasil diselesaikan! Data unit dan karyawan telah disinkronisasi.");
        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan saat import: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
