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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBankTransferHonorariumExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected PayrollPeriod $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekening Honorarium';
    }

    public function collection()
    {
        $honorariums = $this->period->honorariums()->get();

        return $honorariums->map(function ($row, $idx) {
            return [
                'no'            => $idx + 1,
                'nik_nip'       => $row->nik_nip ? "'".$row->nik_nip : '-',
                'nama'          => $row->nama_dosen,
                'nama_bank'     => $row->rekening_bank ?: 'BSI',
                'no_rekening'   => $row->nomor_rekening ? "'".$row->nomor_rekening : '-',
                'atas_nama'     => $row->nama_rekening ?: $row->nama_dosen,
                'total_transfer'=> floatval($row->total_transfer),
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAP DAFTAR TRANSFER REKENING HONORARIUM DOSEN'],
            ['Periode: ' . $this->period->nama_periode . ' (' . $this->period->cutoff_label . ')'],
            [],
            [
                'NO',
                'NIP / NIDN',
                'NAMA DOSEN',
                'NAMA BANK',
                'NO REKENING',
                'ATAS NAMA REKENING',
                'NOMINAL TRANSFER (RP)',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        $headerRow = 5;
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
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
            $sheet->getStyle("A{$headerRow}:G{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
            ]);

            $dataStart = $headerRow + 1;
            if ($highestRow >= $dataStart) {
                $sheet->getStyle("A{$dataStart}:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$dataStart}:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$dataStart}:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$dataStart}:G{$highestRow}")->getFont()->setBold(true);
            }
        }

        return [];
    }
}
