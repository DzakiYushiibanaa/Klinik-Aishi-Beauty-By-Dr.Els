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

class FinanceReportDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public FinanceReportService $service) {}

    public function title(): string { return 'Transaksi'; }

    public function headings(): array
    {
        return [
            'No',
            'Invoice / Kode Order',
            'Tanggal Pembayaran',
            'Customer',
            'Jenis Transaksi',
            'Metode Pembayaran',
            'Status',
            'Subtotal (Rp)',
            'Ongkir (Rp)',
            'Diskon (Rp)',
            'Total Pembayaran (Rp)',
        ];
    }

    public function array(): array
    {
        $rows = $this->service->tableRows();

        $output = [];
        foreach ($rows as $i => $r) {
            $output[] = [
                $i + 1,
                $r['kode_order'],
                $r['paid_at_label'],
                $r['pasien'],
                $r['order_type_label'],
                $r['payment_method_label'],
                $r['status_label'],
                $r['subtotal'],
                $r['ongkir'],
                $r['diskon'],
                $r['grand_total'],
            ];
        }

        $totals = $this->service->tableTotals();
        $output[] = [
            '', '', '', '', '', '', 'TOTAL',
            $totals['subtotal'],
            $totals['ongkir'],
            $totals['diskon'],
            $totals['grand_total'],
        ];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C2622']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:K{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle("A{$highest}:K{$highest}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        foreach (['H', 'I', 'J', 'K'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
