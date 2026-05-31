<?php

namespace App\Exports;

use App\Services\TherapistPerformanceReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TherapistPerformanceDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public TherapistPerformanceReportService $service) {}

    public function title(): string { return 'Kinerja Terapis'; }

    public function headings(): array
    {
        return [
            'No',
            'Nama Terapis',
            'Total Treatment',
            'Total Pasien',
            'Treatment Terfavorit',
            'Total Pendapatan (Rp)',
            'Komisi (%)',
            'Total Komisi (Rp)',
            'Minggu',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = $this->service->tableRows();

        $output = [];
        foreach ($rows as $i => $r) {
            $output[] = [
                $i + 1,
                $r['terapis_name'],
                $r['total_treatment'],
                $r['total_pasien'],
                $r['favorite_treatment'] ?? '—',
                $r['total_revenue'],
                $r['commission_rate'],
                $r['total_commission'],
                $r['weeks_label'],
                ucfirst($r['status_kerja']),
            ];
        }

        $totals = $this->service->tableTotals();
        $output[] = [
            '', 'TOTAL', $totals['total_treatment'], $totals['total_pasien'], '',
            $totals['total_revenue'], '', $totals['total_commission'], '', '',
        ];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C2622']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:J{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle("A{$highest}:J{$highest}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        $sheet->getStyle("C2:C{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D2:D{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F2:F{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G2:G{$highest}")->getNumberFormat()->setFormatCode('0"%"');
        $sheet->getStyle("H2:H{$highest}")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
