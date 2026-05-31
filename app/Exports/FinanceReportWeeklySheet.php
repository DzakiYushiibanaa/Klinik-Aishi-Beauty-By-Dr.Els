<?php

namespace App\Exports;

use App\Services\FinanceReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportWeeklySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public FinanceReportService $service) {}

    public function title(): string { return 'Rekap Mingguan'; }

    public function headings(): array
    {
        return [
            'Minggu',
            'Periode',
            'Total Pendapatan (Rp)',
            'Pendapatan Produk (Rp)',
            'Pendapatan Treatment (Rp)',
            'Pendapatan Konsultasi (Rp)',
            'Total Ongkir (Rp)',
            'Total Transaksi',
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
                $w['total_revenue'],
                $w['revenue_produk'],
                $w['revenue_treatment'],
                $w['revenue_konsultasi'],
                $w['total_ongkir'],
                $w['total_transactions'],
            ];
        }

        $rows[] = [
            'TOTAL', '',
            $weeks->sum('total_revenue'),
            $weeks->sum('revenue_produk'),
            $weeks->sum('revenue_treatment'),
            $weeks->sum('revenue_konsultasi'),
            $weeks->sum('total_ongkir'),
            $weeks->sum('total_transactions'),
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

        foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
