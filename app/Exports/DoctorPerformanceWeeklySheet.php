<?php

namespace App\Exports;

use App\Services\DoctorPerformanceReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorPerformanceWeeklySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public DoctorPerformanceReportService $service) {}

    public function title(): string { return 'Rekap Mingguan'; }

    public function headings(): array
    {
        return [
            'Minggu',
            'Periode',
            'Konsultasi Awal',
            'Konsultasi Lanjutan',
            'Total Treatment',
            'Total Pendapatan (Rp)',
            'Total Komisi (Rp)',
            'Dokter Paling Aktif',
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
                $w['konsultasi_awal'],
                $w['konsultasi_lanjutan'],
                $w['total_treatment'],
                $w['total_revenue'],
                $w['total_commission'],
                $w['best_doctor'] ?? '—',
            ];
        }

        $rows[] = [
            'TOTAL', '',
            $weeks->sum('konsultasi_awal'),
            $weeks->sum('konsultasi_lanjutan'),
            $weeks->sum('total_treatment'),
            $weeks->sum('total_revenue'),
            $weeks->sum('total_commission'),
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B8956F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:H{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle("A{$highest}:H{$highest}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        foreach (['C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
