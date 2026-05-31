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

class TreatmentSalesDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public TreatmentSalesReportService $service)
    {
    }

    public function title(): string
    {
        return 'Detail Treatment';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Order',
            'Nama Pasien',
            'Nama Treatment',
            'Kategori',
            'Harga (Rp)',
            'Jumlah',
            'Total (Rp)',
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
                $r['kode_order'],
                $r['pasien'],
                $r['treatment_name'],
                $r['category'],
                $r['price'],
                $r['qty'],
                $r['total'],
                $r['week_label'],
                $r['period_label'],
            ];
        }

        $totals = $this->service->tableTotals();
        $output[] = ['', '', '', '', 'TOTAL', '', $totals['total_qty'], $totals['total_revenue'], '', ''];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (['A','B','C','D','E','F','G','H','I','J'] as $col) {
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

        $sheet->getStyle("F2:F{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G2:G{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H2:H{$highest}")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
