<?php

namespace App\Exports;

use App\Models\User;
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

class DoctorPerformanceSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        return 'Kinerja Dokter';
    }

    public function query()
    {
        return User::query()
            ->with(['dokterProfile'])
            ->whereHas('dokterProfile')
            
            // Jumlah Pasien
            ->withCount(['appointmentsAsDokter as appointments_as_dokter_count' => function (Builder $query) {
                $query->where('status', 'selesai')
                      ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
            }])
            
            // Total Omset
            ->withSum(['revenueItemsAsDokter as revenue_items_as_dokter_sum_subtotal' => function (Builder $query) {
                $query->whereHas('appointment', function ($q) {
                    $q->where('status', 'selesai')
                      ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                });
            }], 'subtotal')
            
            // Jumlah Treatment
            ->withCount(['revenueItemsAsDokter as treatment_count' => function (Builder $query) {
                $query->where('item_type', 'treatment')
                      ->whereHas('appointment', function ($q) {
                          $q->where('status', 'selesai')
                            ->whereBetween('tanggal_jam', [$this->startDate, $this->endDate]);
                      });
            }])
            
            ->orderByDesc('revenue_items_as_dokter_sum_subtotal');
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Kode Dokter',
            'Nama Dokter',
            'Pengalaman (Tahun)',
            'Status',
            'Total Pasien',
            'Total Omset',
            'Avg per Pasien',
            'Treatment',
        ];
    }

    public function map($user): array
    {
        $this->rowNumber++;
        
        $totalPasien = $user->appointments_as_dokter_count ?? 0;
        $totalOmset = $user->revenue_items_as_dokter_sum_subtotal ?? 0;
        $totalTreatments = $user->treatment_count ?? 0;
        
        $avgRevenue = $totalPasien > 0 ? $totalOmset / $totalPasien : 0;
        
        return [
            $this->rowNumber,
            $user->dokterProfile->kode_dokter ?? '-',
            $user->name,
            $user->dokterProfile->pengalaman_tahun ?? 0,
            $user->dokterProfile->status_praktik ?? '-',
            $totalPasien,
            'Rp ' . number_format($totalOmset, 0, ',', '.'),
            'Rp ' . number_format($avgRevenue, 0, ',', '.'),
            $totalTreatments,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Highlight top 3
        $sheet->getStyle('A2:I2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD700']], // Gold
        ]);
        $sheet->getStyle('A3:I3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0C0C0']], // Silver
        ]);
        $sheet->getStyle('A4:I4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CD7F32']], // Bronze
        ]);

        return [];
    }
}