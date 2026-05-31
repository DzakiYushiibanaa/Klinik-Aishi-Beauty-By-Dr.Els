<?php

namespace App\Exports;

use App\Services\TreatmentSalesReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreatmentSalesSummarySheet implements FromArray, WithStyles, WithTitle
{
    public function __construct(public TreatmentSalesReportService $service)
    {
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        $s = $this->service->summary();

        return [
            ['LAPORAN PENJUALAN TREATMENT', ''],
            ['Periode', $this->service->periodLabel()],
            ['', ''],
            ['Metrik', 'Nilai'],
            ['Total Pendapatan Treatment', 'Rp ' . number_format($s['total_revenue'], 0, ',', '.')],
            ['Total Treatment', number_format($s['total_qty'], 0, ',', '.') . ' sesi'],
            ['Total Transaksi', number_format($s['total_transactions'], 0, ',', '.')],
            ['Treatment Terlaris', $s['best_seller']
                ? ($s['best_seller']['name'] . ' (' . number_format($s['best_seller']['qty'], 0, ',', '.') . ' sesi)')
                : '—'],
            ['Rata-rata Pendapatan per Minggu', 'Rp ' . number_format($s['average_per_week'], 0, ',', '.')],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(36);

        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B8956F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '555555']],
        ]);
        $sheet->getStyle('A4:B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C2622']],
        ]);
        $sheet->getStyle('A5:B9')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);
        return [];
    }
}
