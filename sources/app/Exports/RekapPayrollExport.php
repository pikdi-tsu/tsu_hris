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

class RekapPayrollExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected PayrollPeriod $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekap Payroll';
    }

    public function collection()
    {
        $karyawans = $this->period->karyawans()->get();

        return $karyawans->map(function ($row, $idx) {
            return [
                'no'                    => $idx + 1,
                'nik'                   => $row->nik ?: '-',
                'nama'                  => $row->nama,
                'unit'                  => $row->nama_unit ?: '-',
                'golongan'              => $row->golongan_pangkat ?: '-',
                'jabatan_fungsional'    => $row->jabatan_fungsional ?: '-',
                'jabatan_struktural'    => $row->jabatan_struktural ?: '-',
                'gaji_pokok'            => floatval($row->gaji_pokok),
                'gaji_tetap'            => floatval($row->gaji_tetap),
                'hari_hadir'            => floatval($row->hari_hadir_valid),
                'transport'             => floatval($row->total_transport),
                'lembur'                => floatval($row->total_lembur),
                'gaji_kotor'            => floatval($row->gaji_kotor),
                'hari_izin'             => floatval($row->hari_unpaid_leave),
                'pot_unpaid'            => floatval($row->potongan_unpaid_leave),
                'pot_bpjs'              => floatval($row->potongan_bpjs_kes),
                'pot_lain'              => floatval($row->potongan_lainnya),
                'total_potongan'        => floatval($row->total_potongan),
                'gaji_bersih'           => floatval($row->gaji_bersih),
                'no_rekening'           => $row->no_rekening ? "'".$row->no_rekening : '-',
                'catatan_koreksi'       => $row->catatan_koreksi ?: '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAP PAYROLL BULANAN - ' . strtoupper($this->period->nama_periode)],
            ['Periode Cut-Off: ' . $this->period->cutoff_label],
            [],
            [
                'NO',
                'NIK',
                'NAMA PEGAWAI',
                'UNIT KERJA',
                'GOLONGAN',
                'JABATAN FUNGSIONAL',
                'JABATAN STRUKTURAL',
                'GAJI POKOK (RP)',
                'GAJI TETAP (RP)',
                'HADIR (HARI)',
                'TRANSPORT (RP)',
                'LEMBUR (RP)',
                'TOTAL GAJI KOTOR (RP)',
                'IZIN (HARI)',
                'POT. UNPAID LEAVE (RP)',
                'POT. BPJS KES (RP)',
                'POT. LAINNYA (RP)',
                'TOTAL POTONGAN (RP)',
                'GAJI BERSIH (RP)',
                'NO REKENING',
                'KETERANGAN / CATATAN KOREKSI',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'I' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'O' => '#,##0',
            'P' => '#,##0',
            'Q' => '#,##0',
            'R' => '#,##0',
            'S' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header Titles
        $sheet->mergeCells('A1:U1');
        $sheet->mergeCells('A2:U2');
        $sheet->mergeCells('A3:U3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Header Table
        $headerRow = 5;
        $sheet->getStyle("A{$headerRow}:U{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'], // Dark Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:U{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
            ]);
        }

        // Tanda Tangan & Timestamp Approval Bertingkat pada Excel
        $signRow = $highestRow + 3;
        $sheet->setCellValue("B{$signRow}", "1. Dibuat Oleh (Pembuat Draft):");
        $sheet->setCellValue("G{$signRow}", "2. Diperiksa Oleh (Validator 1):");
        $sheet->setCellValue("L{$signRow}", "3. Diverifikasi Oleh (Validator 2):");
        $sheet->setCellValue("Q{$signRow}", "4. Disetujui Oleh (Approval Final):");

        $sheet->getStyle("B{$signRow}:Q{$signRow}")->getFont()->setBold(true);

        $logDraft = $this->period->approvals->where('action', 'submitted')->sortByDesc('created_at')->first();
        $logVal1 = $this->period->approvals->where('step', 'validator_1')->where('action', 'approved')->sortByDesc('created_at')->first();
        $logVal2 = $this->period->approvals->where('step', 'validator_2')->where('action', 'approved')->sortByDesc('created_at')->first();
        $logApproval = $this->period->approvals->where('step', 'approval')->where('action', 'approved')->sortByDesc('created_at')->first();

        $tglDraft = $logDraft ? $logDraft->created_at->format('d/m/Y H:i') : ($this->period->created_at ? $this->period->created_at->format('d/m/Y H:i') : '-');
        $tglVal1 = $logVal1 ? $logVal1->created_at->format('d/m/Y H:i') : '-';
        $tglVal2 = $logVal2 ? $logVal2->created_at->format('d/m/Y H:i') : '-';
        $tglAppr = $logApproval ? $logApproval->created_at->format('d/m/Y H:i') : '-';

        $statusRow = $signRow + 1;
        $sheet->setCellValue("B{$statusRow}", "[Submitted: " . $tglDraft . "]");
        $sheet->setCellValue("G{$statusRow}", ($logVal1 ? "[Approved: " . $tglVal1 . "]" : "[Menunggu]"));
        $sheet->setCellValue("L{$statusRow}", ($logVal2 ? "[Approved: " . $tglVal2 . "]" : "[Menunggu]"));
        $sheet->setCellValue("Q{$statusRow}", ($logApproval ? "[Approved & Locked: " . $tglAppr . "]" : "[Menunggu]"));

        $nameRow = $signRow + 4;
        $sheet->setCellValue("B{$nameRow}", $this->period->createdUser->name ?? 'Admin');
        $sheet->setCellValue("G{$nameRow}", $this->period->validator1->nama ?? '-');
        $sheet->setCellValue("L{$nameRow}", $this->period->validator2->nama ?? '-');
        $sheet->setCellValue("Q{$nameRow}", $this->period->approvalKaryawan->nama ?? '-');

        $sheet->getStyle("B{$nameRow}:Q{$nameRow}")->getFont()->setBold(true)->setUnderline(true);

        return [];
    }
}
