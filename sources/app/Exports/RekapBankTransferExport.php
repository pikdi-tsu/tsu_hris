<?php

namespace App\Exports;

use App\Models\PayrollPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBankTransferExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected PayrollPeriod $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekening Payroll';
    }

    public function collection()
    {
        $karyawans = $this->period->karyawans()->get();

        return $karyawans->map(function ($row, $idx) {
            return [
                'no'            => $idx + 1,
                'nama'          => $row->nama,
                'no_rekening'   => $row->no_rekening ? "'".$row->no_rekening : '-',
                'nama_bank'     => $row->nama_bank ?: 'Bank Mandiri',
                'atas_nama'     => $row->atas_nama_rekening ?: $row->nama,
                'total_transfer'=> floatval($row->gaji_bersih),
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAP DAFTAR TRANSFER REKENING PAYROLL PEGAWAI'],
            ['Periode: ' . $this->period->nama_periode . ' (' . $this->period->cutoff_label . ')'],
            [],
            [
                'NO',
                'NAMA PEGAWAI',
                'NO REKENING',
                'NAMA BANK',
                'ATAS NAMA REKENING',
                'NOMINAL TRANSFER (RP)',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        $headerRow = 5;
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF047857'], // Dark Emerald Green
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:F{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
            ]);
        }

        return [];
    }
}
