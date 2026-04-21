<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return [
            'Metrik',
            'Nilai (Rp)',
        ];
    }

    public function collection()
    {
        $orderQuery = Order::query()
            ->where('status', 'sudah_dibayar')
            ->whereBetween('paid_at', [$this->startDate, $this->endDate]);

        $totalRevenue = (clone $orderQuery)->sum('grand_total');
        $totalOngkir = (clone $orderQuery)->sum('ongkir');
        $totalDiskon = (clone $orderQuery)->sum('diskon_total');
        $totalTransaksi = (clone $orderQuery)->count();
        $realRevenue = $totalRevenue - $totalOngkir;
        $aov = $totalTransaksi > 0 ? $totalRevenue / $totalTransaksi : 0;

        $itemQuery = OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->where('status', 'sudah_dibayar')
                  ->whereBetween('paid_at', [$this->startDate, $this->endDate]);
            });

        $productRevenue = (clone $itemQuery)->where('item_type', 'product')->sum('subtotal');
        $treatmentRevenue = (clone $itemQuery)->where('item_type', 'treatment')->sum('subtotal');
        $bundleRevenue = (clone $itemQuery)->where('item_type', 'bundle')->sum('subtotal');
        $konsultasiRevenue = (clone $itemQuery)->where('item_type', 'konsultasi')->sum('subtotal');
        $membershipRevenue = (clone $itemQuery)->where('item_type', 'membership')->sum('subtotal');

        return collect([
            ['Periode', $this->startDate->format('d/m/Y') . ' - ' . $this->endDate->format('d/m/Y')],
            ['', ''],
            ['OVERVIEW', ''],
            ['Total Transaksi', number_format($totalTransaksi, 0, ',', '.')],
            ['Omset Bruto (+ Ongkir)', 'Rp ' . number_format($totalRevenue, 0, ',', '.')],
            ['Total Ongkir', 'Rp ' . number_format($totalOngkir, 0, ',', '.')],
            ['Pendapatan Bersih (Excl. Ongkir)', 'Rp ' . number_format($realRevenue, 0, ',', '.')],
            ['Total Diskon Diberikan', 'Rp ' . number_format($totalDiskon, 0, ',', '.')],
            ['AOV (Rata-rata per Transaksi)', 'Rp ' . number_format($aov, 0, ',', '.')],
            ['', ''],
            ['BREAKDOWN REVENUE', ''],
            ['Revenue Treatment', 'Rp ' . number_format($treatmentRevenue, 0, ',', '.')],
            ['Revenue Produk', 'Rp ' . number_format($productRevenue, 0, ',', '.')],
            ['Revenue Bundle', 'Rp ' . number_format($bundleRevenue, 0, ',', '.')],
            ['Revenue Konsultasi', 'Rp ' . number_format($konsultasiRevenue, 0, ',', '.')],
            ['Revenue Membership', 'Rp ' . number_format($membershipRevenue, 0, ',', '.')],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(25);

        // Header style
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Section headers (OVERVIEW, BREAKDOWN REVENUE)
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
        ]);
        $sheet->getStyle('A11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
        ]);

        // Highlight key metrics
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E6C9']],
        ]);

        return [];
    }
}