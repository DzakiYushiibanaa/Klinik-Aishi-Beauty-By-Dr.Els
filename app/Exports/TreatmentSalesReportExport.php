<?php

namespace App\Exports;

use App\Services\TreatmentSalesReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TreatmentSalesReportExport implements WithMultipleSheets
{
    public function __construct(public TreatmentSalesReportService $service)
    {
    }

    public function sheets(): array
    {
        return [
            new TreatmentSalesSummarySheet($this->service),
            new TreatmentSalesDetailSheet($this->service),
            new TreatmentSalesWeeklySheet($this->service),
        ];
    }
}
