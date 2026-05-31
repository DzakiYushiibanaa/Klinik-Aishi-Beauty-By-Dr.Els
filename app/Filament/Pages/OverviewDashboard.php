<?php

namespace App\Filament\Pages;

use App\Services\AdminDashboardService;
use BackedEnum;
use Filament\Pages\Dashboard;

/**
 * Custom Admin Dashboard untuk Aishi Beauty.
 * Menggantikan Filament default Dashboard.
 */
class OverviewDashboard extends Dashboard
{
    protected string $view = 'filament.pages.overview-dashboard';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dasbor';

    protected static ?int $navigationSort = -10;

    /** Disable Filament default widgets — kita render manual via blade. */
    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Dashboard Overview';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getViewDataProperty(): array
    {
        $service = new AdminDashboardService();

        return [
            'service' => $service,
            'today' => $service->todayLabel(),
            'greeting' => $service->greeting(),
            'adminName' => $service->adminName(),
            'stats' => $service->todayStats(),
            'chartRevenue' => $service->revenueLastWeek(),
            'chartAppointments' => $service->appointmentsLastWeek(),
            'chartComposition' => $service->revenueComposition(),
            'todaySchedule' => $service->todaySchedule(),
            'recentAppointments' => $service->recentAppointments(),
            'recentActivities' => $service->recentActivities(),
            'reminders' => $service->reminders(),
            'topPerformance' => $service->topPerformance(),
            'quickStats' => $service->quickStats(),
        ];
    }
}
