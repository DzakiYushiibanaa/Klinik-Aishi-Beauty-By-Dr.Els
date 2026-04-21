<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\Eloquent\Builder;

class TherapistPerformanceSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        return 'Kinerja Terapis';
    }

    public function query()
    {
        return User::query()
            ->with(['terapisProfile'])
            ->whereHas('terapisProfile')
            
            // Jumlah Pasien
            ->withCount(['appointmentsAsTerapis as appointments_as_terapis_count' => function (Builder $query) {
                $query->where('status', 'selesai')
                      ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
            }])
            
            // Total Jasa
            ->withSum(['revenueItemsAsTerapis as revenue_items_as_terapis_sum_subtotal' => function (Builder $query) {
                $query->whereHas('appointment', function ($q) {
                    $q->where('status', 'selesai')
                      ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                });
            }], 'subtotal')
            
            // Jumlah Treatment
            ->withCount(['revenueItemsAsTerapis as treatment_count' => function (Builder $query) {
                $query->where('item_type', 'treatment')
                      ->whereHas('appointment', function ($q) {
                          $q->where('status', 'selesai')
                            ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                      });
            }])
            
            ->orderByDesc('revenue_items_as_terapis_sum_subtotal');
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Kode Terapis',
            'Nama Terapis',
            'Jabatan',
            'Pengalaman (Tahun)',
            'Status',
            'Total Pasien',
            'Total Jasa',
            'Avg per Pasien',
            'Treatment',
        ];
    }

    public function map($user): array
    {
        $this->rowNumber++;
        
        $totalPasien = $user->appointments_as_terapis_count ?? 0;
        $totalJasa = $user->revenue_items_as_terapis_sum_subtotal ?? 0;
        $totalTreatments = $user->treatment_count ?? 0;
        
        $avgRevenue = $totalPasien > 0 ? $totalJasa / $totalPasien : 0;
        
        return [
            $this->rowNumber,
            $user->terapisProfile->kode_terapis ?? '-',
            $user->name,
            $user->terapisProfile->jabatan ?? '-',
            $user->terapisProfile->pengalaman_tahun ?? 0,
            $user->terapisProfile->status_kerja ?? '-',
            $totalPasien,
            'Rp ' . number_format($totalJasa, 0, ',', '.'),
            'Rp ' . number_format($avgRevenue, 0, ',', '.'),
            $totalTreatments,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Highlight top 3
        $sheet->getStyle('A2:J2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD700']], // Gold
        ]);
        $sheet->getStyle('A3:J3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0C0C0']], // Silver
        ]);
        $sheet->getStyle('A4:J4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CD7F32']], // Bronze
        ]);

        return [];
    }
}