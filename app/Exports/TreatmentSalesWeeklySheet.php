<?php

namespace App\Exports;

use App\Services\TreatmentSalesReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreatmentSalesWeeklySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public TreatmentSalesReportService $service)
    {
    }

    public function title(): string
    {
        return 'Rekap Mingguan';
    }

    public function headings(): array
    {
        return [
            'Minggu',
            'Periode',
            'Total Treatment',
            'Total Pendapatan (Rp)',
            'Treatment Terlaris',
        ];
    }

    public function array(): array
    {
        $weeks = $this->service->weeklyRecap();

        $rows = [];
        foreach ($weeks as $w) {
            $rows[] = [
                $w['label'],
                $w['range'],
                $w['total_qty'],
                $w['total_revenue'],
                $w['best_treatment'] ?? '—',
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            $weeks->sum('total_qty'),
            $weeks->sum('total_revenue'),
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (['A','B','C','D','E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B8956F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:E{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle("A{$highest}:E{$highest}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        $sheet->getStyle("C2:C{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D2:D{$highest}")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
