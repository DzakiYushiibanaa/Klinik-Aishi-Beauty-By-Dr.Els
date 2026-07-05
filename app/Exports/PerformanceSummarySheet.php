<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PerformanceSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
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
        return 'Ringkasan Kinerja';
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Nilai',
        ];
    }

    public function collection()
    {
        // DOKTER STATS
        $dokters = User::query()
            ->whereHas('dokterProfile')
            ->withCount(['appointmentsAsDokter as appointments_as_dokter_count' => function ($q) {
                $q->where('status', 'selesai')
                  ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
            }])
            ->withSum(['revenueItemsAsDokter as revenue_items_as_dokter_sum_subtotal' => function ($q) {
                $q->whereHas('appointment', function ($query) {
                    $query->where('status', 'selesai')
                          ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                });
            }], 'subtotal')
            ->get();

        $totalDokter = $dokters->count();
        $totalPasienDokter = $dokters->sum('appointments_as_dokter_count');
        $totalOmsetDokter = $dokters->sum('revenue_items_as_dokter_sum_subtotal');
        $avgOmsetDokter = $totalDokter > 0 ? $totalOmsetDokter / $totalDokter : 0;

        // TERAPIS STATS
        $terapis = User::query()
            ->whereHas('terapisProfile')
            ->withCount(['appointmentsAsTerapis as appointments_as_terapis_count' => function ($q) {
                $q->where('status', 'selesai')
                  ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
            }])
            ->withSum(['revenueItemsAsTerapis as revenue_items_as_terapis_sum_subtotal' => function ($q) {
                $q->whereHas('appointment', function ($query) {
                    $query->where('status', 'selesai')
                          ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                });
            }], 'subtotal')
            ->get();

        $totalTerapis = $terapis->count();
        $totalPasienTerapis = $terapis->sum('appointments_as_terapis_count');
        $totalJasaTerapis = $terapis->sum('revenue_items_as_terapis_sum_subtotal');
        $avgJasaTerapis = $totalTerapis > 0 ? $totalJasaTerapis / $totalTerapis : 0;

        // TOP PERFORMERS
        $topDokter = $dokters->sortByDesc('revenue_items_as_dokter_sum_subtotal')->first();
        $topTerapis = $terapis->sortByDesc('revenue_items_as_terapis_sum_subtotal')->first();

        return collect([
            ['Periode', $this->startDate->format('d/m/Y') . ' - ' . $this->endDate->format('d/m/Y')],
            ['', ''],
            
            ['=== KINERJA DOKTER ===', ''],
            ['Total Dokter Aktif', $totalDokter],
            ['Total Pasien Ditangani', number_format($totalPasienDokter, 0, ',', '.')],
            ['Total Omset Dihasilkan', 'Rp ' . number_format($totalOmsetDokter, 0, ',', '.')],
            ['Rata-rata Omset per Dokter', 'Rp ' . number_format($avgOmsetDokter, 0, ',', '.')],
            ['Dokter Terbaik', $topDokter ? $topDokter->name : '-'],
            ['Omset Dokter Terbaik', $topDokter ? 'Rp ' . number_format($topDokter->revenue_items_as_dokter_sum_subtotal, 0, ',', '.') : '-'],
            ['', ''],
            
            ['=== KINERJA TERAPIS ===', ''],
            ['Total Terapis Aktif', $totalTerapis],
            ['Total Pasien Ditangani', number_format($totalPasienTerapis, 0, ',', '.')],
            ['Total Jasa Dihasilkan', 'Rp ' . number_format($totalJasaTerapis, 0, ',', '.')],
            ['Rata-rata Jasa per Terapis', 'Rp ' . number_format($avgJasaTerapis, 0, ',', '.')],
            ['Terapis Terbaik', $topTerapis ? $topTerapis->name : '-'],
            ['Jasa Terapis Terbaik', $topTerapis ? 'Rp ' . number_format($topTerapis->revenue_items_as_terapis_sum_subtotal, 0, ',', '.') : '-'],
            ['', ''],
            
            ['=== TOTAL KESELURUHAN ===', ''],
            ['Total Revenue SDM', 'Rp ' . number_format($totalOmsetDokter + $totalJasaTerapis, 0, ',', '.')],
            ['Total Pasien Dilayani', number_format($totalPasienDokter + $totalPasienTerapis, 0, ',', '.')],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(30);

        // Header
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Section Headers
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
        ]);
        $sheet->getStyle('A11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
        ]);
        $sheet->getStyle('A19')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9C27B0']],
        ]);

        // Highlight Total Revenue
        $sheet->getStyle('A20:B20')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1B5E20']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E6C9']],
        ]);

        return [];
    }
}
