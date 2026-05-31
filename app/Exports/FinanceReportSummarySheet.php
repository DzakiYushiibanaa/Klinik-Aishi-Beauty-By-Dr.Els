<?php

namespace App\Exports;

use App\Services\FinanceReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportSummarySheet implements FromArray, WithStyles, WithTitle
{
    public function __construct(public FinanceReportService $service) {}

    public function title(): string { return 'Ringkasan'; }

    public function array(): array
    {
        $s = $this->service->summary();

        return [
            ['LAPORAN KEUANGAN', ''],
            ['Periode', $this->service->periodLabel()],
            ['', ''],
            ['Metrik', 'Nilai'],
            ['Total Pendapatan (Subtotal − Diskon)', 'Rp ' . number_format($s['total_revenue'], 0, ',', '.')],
            ['Pendapatan Produk', 'Rp ' . number_format($s['revenue_produk'], 0, ',', '.')],
            ['Pendapatan Treatment', 'Rp ' . number_format($s['revenue_treatment'], 0, ',', '.')],
            ['Pendapatan Konsultasi', 'Rp ' . number_format($s['revenue_konsultasi'], 0, ',', '.')],
            ['Pendapatan Membership', 'Rp ' . number_format($s['revenue_membership'], 0, ',', '.')],
            ['Total Ongkir Pengiriman', 'Rp ' . number_format($s['total_ongkir'], 0, ',', '.')],
            ['Total Transaksi Sudah Dibayar', number_format($s['total_transactions'], 0, ',', '.') . ' transaksi'],
            ['', ''],
            ['Catatan', 'Ongkir tidak dihitung sebagai revenue klinik.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(40);
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
        $sheet->getStyle('A5:B11')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);
        $sheet->getStyle('A13:B13')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '888888']],
        ]);
        return [];
    }
}
