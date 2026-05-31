<?php

namespace App\Exports;

use App\Services\FinanceReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinanceReportExport implements WithMultipleSheets
{
    public function __construct(public FinanceReportService $service) {}

    public function sheets(): array
    {
        return [
            new FinanceReportSummarySheet($this->service),
            new FinanceReportDetailSheet($this->service),
            new FinanceReportWeeklySheet($this->service),
        ];
    }
}
