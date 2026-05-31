<?php

namespace App\Exports;

use App\Services\TherapistPerformanceReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TherapistPerformanceSummarySheet implements FromArray, WithStyles, WithTitle
{
    public function __construct(public TherapistPerformanceReportService $service) {}

    public function title(): string { return 'Ringkasan'; }

    public function array(): array
    {
        $s = $this->service->summary();

        return [
            ['LAPORAN KINERJA TERAPIS', ''],
            ['Periode', $this->service->periodLabel()],
            ['', ''],
            ['Metrik', 'Nilai'],
            ['Total Terapis Aktif', number_format($s['total_active'], 0, ',', '.')],
            ['Total Treatment Ditangani', number_format($s['total_treatment'], 0, ',', '.') . ' sesi'],
            ['Terapis Terproduktif', $s['best_therapist']
                ? ($s['best_therapist']['name'] . ' (' . $s['best_therapist']['count'] . ' sesi)')
                : '—'],
            ['Total Pendapatan', 'Rp ' . number_format($s['total_revenue'], 0, ',', '.')],
            ['Total Komisi Terapis', 'Rp ' . number_format($s['total_commission'], 0, ',', '.')],
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
