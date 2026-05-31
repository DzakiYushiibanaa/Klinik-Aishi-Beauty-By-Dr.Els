<?php

namespace App\Exports;

use App\Services\TherapistPerformanceReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TherapistPerformanceExport implements WithMultipleSheets
{
    public function __construct(public TherapistPerformanceReportService $service)
    {
    }

    public function sheets(): array
    {
        return [
            new TherapistPerformanceSummarySheet($this->service),
            new TherapistPerformanceDetailSheet($this->service),
            new TherapistPerformanceWeeklySheet($this->service),
        ];
    }
}
