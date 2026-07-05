<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransactionDetailsSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $rowNumber = 0;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Detail Transaksi';
    }

    public function query()
    {
        return Order::query()
            ->with(['pasien', 'dibuatOleh'])
            ->where('status', 'sudah_dibayar')
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->orderBy('paid_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Order',
            'Tanggal Bayar',
            'Nama Pasien',
            'Metode Pembayaran',
            'Channel',
            'Subtotal',
            'Diskon',
            'Ongkir',
            'Grand Total',
            'Status',
            'Dibuat Oleh',
        ];
    }

    public function map($order): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $order->kode_order ?? '-',
            $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : '-',
            $order->pasien->name ?? '-',
            $order->payment_method ?? '-',
            $order->payment_channel ?? '-',
            'Rp ' . number_format($order->subtotal, 0, ',', '.'),
            'Rp ' . number_format($order->diskon_total, 0, ',', '.'),
            'Rp ' . number_format($order->ongkir, 0, ',', '.'),
            'Rp ' . number_format($order->grand_total, 0, ',', '.'),
            ucfirst($order->status),
            $order->dibuatOleh->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
        ]);

        return [];
    }
}
