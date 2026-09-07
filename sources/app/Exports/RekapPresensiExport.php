<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPresensiExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $periodeText;
    protected $nominal;

    public function __construct($data, $periodeText, $nominal = 20000)
    {
        $this->data = $data;
        $this->periodeText = $periodeText;
        $this->nominal = $nominal;
    }

    public function view(): View
    {
        return view('admin::absensi.rekap_excel', [
            'data' => $this->data,
            'periodeText' => $this->periodeText,
            'nominal' => $this->nominal,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
        ];
    }
}
