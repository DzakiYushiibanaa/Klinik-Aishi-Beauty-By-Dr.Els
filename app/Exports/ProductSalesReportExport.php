<?php

namespace App\Exports;

use App\Services\ProductSalesReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductSalesReportExport implements WithMultipleSheets
{
    public function __construct(public ProductSalesReportService $service)
    {
    }

    public function sheets(): array
    {
        return [
            new ProductSalesSummarySheet($this->service),
            new ProductSalesDetailSheet($this->service),
            new ProductSalesWeeklySheet($this->service),
        ];
    }
}
