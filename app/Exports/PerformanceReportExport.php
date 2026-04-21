<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class PerformanceReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $this->endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate)->endOfDay();
    }

    public function sheets(): array
    {
        return [
            new PerformanceSummarySheet($this->startDate, $this->endDate),
            new DoctorPerformanceSheet($this->startDate, $this->endDate),
            new TherapistPerformanceSheet($this->startDate, $this->endDate),
        ];
    }
}