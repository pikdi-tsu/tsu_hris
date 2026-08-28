<?php

namespace App\Exports;

use App\Models\LemburKaryawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RiwayatLemburExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return LemburKaryawan::query()
            ->with(['masterLembur', 'user', 'atasan', 'hrd'])
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Karyawan',
            'NIK',
            'Jenis Lembur',
            'Tanggal Lembur',
            'Jam Mulai',
            'Jam Selesai',
            'Total Jam',
            'Keterangan',
            'Status Atasan',
            'Alasan Atasan',
            'Nama Atasan',
            'Status SDM',
            'Alasan SDM',
            'Nama SDM',
            'Dibuat Pada'
        ];
    }

    public function map($lembur): array
    {
        return [
            $lembur->id,
            $lembur->user ? $lembur->user->nama : '-',
            $lembur->user ? $lembur->user->nik : '-',
            $lembur->masterLembur ? $lembur->masterLembur->jenislembur : '-',
            $lembur->tanggal_lembur ? Carbon::parse($lembur->tanggal_lembur)->format('Y-m-d') : '-',
            $lembur->jam_mulai ? Carbon::parse($lembur->jam_mulai)->format('H:i') : '-',
            $lembur->jam_selesai ? Carbon::parse($lembur->jam_selesai)->format('H:i') : '-',
            $lembur->total_jam,
            $lembur->keterangan,
            $lembur->statusatasan,
            $lembur->alasanatasan,
            $lembur->atasan ? $lembur->atasan->nama : '-',
            $lembur->statushrd,
            $lembur->alasanhrd,
            $lembur->hrd ? $lembur->hrd->nama : '-',
            $lembur->created_at ? Carbon::parse($lembur->created_at)->format('Y-m-d H:i:s') : '-',
        ];
    }
}
