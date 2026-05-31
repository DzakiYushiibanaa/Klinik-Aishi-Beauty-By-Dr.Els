<?php

namespace App\Exports;

use App\Services\ProductSalesReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductSalesDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public ProductSalesReportService $service)
    {
    }

    public function title(): string
    {
        return 'Detail Produk';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Produk',
            'Kategori',
            'Harga (Rp)',
            'Jumlah Terjual',
            'Total Harga (Rp)',
            'Minggu',
            'Periode Tanggal',
        ];
    }

    public function array(): array
    {
        $rows = $this->service->tableRows();

        $output = [];
        foreach ($rows as $i => $r) {
            $output[] = [
                $i + 1,
                $r['name'],
                $r['category'],
                $r['price'],
                $r['qty'],
                $r['total'],
                $r['week_label'],
                $r['period_label'],
            ];
        }

        // Footer total
        $totals = $this->service->tableTotals();
        $output[] = ['', '', 'TOTAL', '', $totals['total_qty'], $totals['total_revenue'], '', ''];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (['A','B','C','D','E','F','G','H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C2622']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:H{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        // Total row (last row)
        $sheet->getStyle("A{$highest}:H{$highest}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        // Number columns formatting
        $sheet->getStyle("D2:D{$highest}")->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle("E2:E{$highest}")->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle("F2:F{$highest}")->getNumberFormat()
            ->setFormatCode('#,##0');

        return [];
    }
}
