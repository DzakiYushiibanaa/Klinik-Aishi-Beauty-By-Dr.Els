<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class FinancialReportExport implements WithMultipleSheets
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
            new SummarySheet($this->startDate, $this->endDate),
            new TransactionDetailsSheet($this->startDate, $this->endDate),
            // new ItemBreakdownSheet($this->startDate, $this->endDate),
            // new BestSellersSheet($this->startDate, $this->endDate),
            // new TreatmentPerformanceSheet($this->startDate, $this->endDate),
            // new PaymentMethodSheet($this->startDate, $this->endDate),
            // new PromoPerformanceSheet($this->startDate, $this->endDate),
        ];
    }
}
