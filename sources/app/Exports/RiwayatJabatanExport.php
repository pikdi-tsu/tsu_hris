<?php

namespace App\Exports;

use App\Models\RiwayatJabatan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RiwayatJabatanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $karyawanId;

    public function __construct($karyawanId = null)
    {
        $this->karyawanId = $karyawanId;
    }

    public function query()
    {
        $query = RiwayatJabatan::query()
            ->with(['dataDosenTendik', 'jabatanStruktural', 'jabatanFungsional', 'pangkatGolongan'])
            ->orderBy('created_at', 'desc');

        if ($this->karyawanId) {
            $query->where('data_dosen_tendik_id', $this->karyawanId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'NAMA PEGAWAI',
            'NIK',
            'TIPE JABATAN',
            'NAMA JABATAN',
            'PANGKAT / GOLONGAN',
            'TANGGAL MULAI',
            'TANGGAL SELESAI',
            'DURASI (BULAN)',
            'KETERANGAN MUTASI',
        ];
    }

    public function map($riwayat): array
    {
        $jabatanName = '-';
        $pangkatGolongan = '-';

        if ($riwayat->tipe_jabatan === 'struktural') {
            $jabatanName = $riwayat->jabatanStruktural->nama_jabatan ?? 'Unknown';
        } else {
            $jabatanName = $riwayat->jabatanFungsional->nama_jabatan ?? 'Unknown';
            if ($riwayat->pangkatGolongan) {
                $pangkatGolongan = $riwayat->pangkatGolongan->nama_pangkat . ' (Gol. ' . $riwayat->pangkatGolongan->golongan . ')';
            }
        }

        return [
            $riwayat->dataDosenTendik->nama ?? 'Unknown',
            $riwayat->dataDosenTendik->nik ?? '-',
            strtoupper($riwayat->tipe_jabatan),
            $jabatanName,
            $pangkatGolongan,
            $riwayat->tgl_mulai ? Carbon::parse($riwayat->tgl_mulai)->format('d-m-Y') : '-',
            $riwayat->tgl_selesai ? Carbon::parse($riwayat->tgl_selesai)->format('d-m-Y') : 'Sekarang',
            $riwayat->lama_menjabat_bulan ?? '< 1',
            $riwayat->keterangan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
