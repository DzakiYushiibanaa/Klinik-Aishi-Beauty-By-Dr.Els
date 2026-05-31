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

class DoctorPerformanceDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(public DoctorPerformanceReportService $service) {}

    public function title(): string { return 'Kinerja Dokter'; }

    public function headings(): array
    {
        return [
            'No',
            'Nama Dokter',
            'Konsultasi Awal',
            'Konsultasi Lanjutan',
            'Total Konsultasi',
            'Treatment Ditangani',
            'Total Treatment',
            'Total Pasien',
            'Pendapatan Konsultasi (Rp)',
            'Pendapatan Treatment (Rp)',
            'Total Pendapatan (Rp)',
            'Komisi Konsultasi (15%)',
            'Komisi Treatment (10%)',
            'Total Komisi (Rp)',
            'Minggu',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = $this->service->tableRows();

        $output = [];
        foreach ($rows as $i => $r) {
            $treatmentList = collect($r['treatment_list'])
                ->map(fn ($t) => "{$t['name']} {$t['qty']}x")
                ->implode(', ');

            $output[] = [
                $i + 1,
                $r['dokter_name'],
                $r['konsultasi_awal'],
                $r['konsultasi_lanjutan'],
                $r['total_konsultasi'],
                $treatmentList ?: '—',
                $r['total_treatment'],
                $r['total_pasien'],
                $r['revenue_konsultasi'],
                $r['revenue_treatment'],
                $r['total_revenue'],
                $r['komisi_konsultasi'],
                $r['komisi_treatment'],
                $r['total_komisi'],
                $r['weeks_label'],
                ucfirst($r['status_praktik']),
            ];
        }

        $totals = $this->service->tableTotals();
        $output[] = [
            '', 'TOTAL',
            $totals['konsultasi_awal'],
            $totals['konsultasi_lanjutan'],
            $totals['total_konsultasi'],
            '',
            $totals['total_treatment'],
            $totals['total_pasien'],
            '', '',
            $totals['total_revenue'],
            $totals['komisi_konsultasi'],
            $totals['komisi_treatment'],
            $totals['total_komisi'],
            '', '',
        ];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C2622']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highest = $sheet->getHighestRow();
        $sheet->getStyle("A1:P{$highest}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle("A{$highest}:P{$highest}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2EFE9']],
        ]);

        // Number columns
        foreach (['C', 'D', 'E', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highest}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
