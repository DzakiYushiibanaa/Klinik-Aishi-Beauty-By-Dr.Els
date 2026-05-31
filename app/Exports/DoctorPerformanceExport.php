<?php

namespace App\Exports;

use App\Services\DoctorPerformanceReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DoctorPerformanceExport implements WithMultipleSheets
{
    public function __construct(public DoctorPerformanceReportService $service) {}

    public function sheets(): array
    {
        return [
            new DoctorPerformanceSummarySheet($this->service),
            new DoctorPerformanceDetailSheet($this->service),
            new DoctorPerformanceWeeklySheet($this->service),
        ];
    }
}
