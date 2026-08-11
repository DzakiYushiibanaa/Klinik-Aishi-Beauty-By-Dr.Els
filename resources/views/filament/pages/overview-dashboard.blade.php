@php
    $data = $this->viewData;
    $stats = $data['stats'];
    $chartRevenue = $data['chartRevenue'];
    $chartAppointments = $data['chartAppointments'];
    $chartComposition = $data['chartComposition'];
    $todaySchedule = $data['todaySchedule'];
    $recentAppointments = $data['recentAppointments'];
    $recentActivities = $data['recentActivities'];
    $reminders = $data['reminders'];
    $topPerformance = $data['topPerformance'];
    $quickStats = $data['quickStats'];
    $rupiah = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $apptUrl = url('/admin/appointments/create');
    $patientUrl = url('/admin/pasiens/create');
    $productUrl = url('/admin/products/create');
    $treatmentUrl = url('/admin/treatments/create');
    $allScheduleUrl = url('/admin/appointments');
@endphp

<x-filament-panels::page>
    @push('scripts')
        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endonce
    @endpush

    @include('filament.pages._partials._dash_styles')

    <div class="dash">
        @include('filament.pages._partials._dash_header')
        @if (auth()->user()?->hasAnyRole(['admin', 'super_admin']))
            @include('filament.pages._partials._dash_top_row')
            @include('filament.pages._partials._dash_stats')
            @include('filament.pages._partials._dash_schedule')
            @include('filament.pages._partials._dash_charts')
        @endif
    </div>

    {{-- ============ CHART INIT ============ --}}
    @push('scripts')
        <script>
            (function () {
                function rupiah(n) {
                    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
                }

                const palette = ['#B8956F', '#3B3529', '#8C7A63', '#7C3AED', '#059669', '#2563EB'];
                const FONT_FAMILY = "'Plus Jakarta Sans', sans-serif";

                function renderDashCharts() {
                    if (typeof Chart === 'undefined') return;

                    document.querySelectorAll('.dash canvas').forEach(c => {
                        let id = c.id;
                        let labels = [], dataVals = [];
                        try {
                            labels = JSON.parse(c.dataset.labels || '[]');
                            dataVals = JSON.parse(c.dataset.values || '[]');
                        } catch (e) { }

                        let ctx = c.getContext('2d');
                        if (id === 'dash-chart-revenue') {
                            let grad = ctx.createLinearGradient(0, 0, 0, 300);
                            grad.addColorStop(0, 'rgba(184, 149, 111, 0.4)');
                            grad.addColorStop(1, 'rgba(184, 149, 111, 0.01)');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Pendapatan',
                                        data: dataVals,
                                        borderColor: '#B8956F',
                                        backgroundColor: grad,
                                        borderWidth: 3,
                                        fill: true,
                                        pointBackgroundColor: '#fff',
                                        pointBorderColor: '#B8956F',
                                        pointBorderWidth: 2,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: '#3B3529',
                                            padding: 12,
                                            titleFont: { family: FONT_FAMILY, size: 13, weight: 600 },
                                            bodyFont: { family: FONT_FAMILY, size: 13 },
                                            callbacks: {
                                                label: function (ctx) { return rupiah(ctx.raw); }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                                            ticks: {
                                                font: { family: FONT_FAMILY, size: 11 },
                                                color: '#94A3B8',
                                                callback: function (v) { return rupiah(v); }
                                            }
                                        },
                                        x: {
                                            grid: { display: false, drawBorder: false },
                                            ticks: {
                                                font: { family: FONT_FAMILY, size: 11 },
                                                color: '#94A3B8',
                                                maxTicksLimit: 7
                                            }
                                        }
                                    }
                                }
                            });
                        } else if (id === 'dash-chart-appointments') {
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Appointments',
                                        data: dataVals,
                                        backgroundColor: '#3B3529',
                                        borderRadius: 6,
                                        barThickness: 'flex',
                                        maxBarThickness: 45
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: '#3B3529',
                                            padding: 12,
                                            titleFont: { family: FONT_FAMILY, size: 13, weight: 600 },
                                            bodyFont: { family: FONT_FAMILY, size: 13 }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                                            ticks: {
                                                stepSize: 1,
                                                font: { family: FONT_FAMILY, size: 11 },
                                                color: '#94A3B8'
                                            }
                                        },
                                        x: {
                                            grid: { display: false, drawBorder: false },
                                            ticks: {
                                                font: { family: FONT_FAMILY, size: 11 },
                                                color: '#94A3B8',
                                                maxTicksLimit: 7
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                }

                // Filament lifecycle hook: re-render on navigation
                document.addEventListener('livewire:navigated', () => {
                    setTimeout(() => {
                        if (document.querySelector('#dash-chart-revenue')) {
                            renderDashCharts();
                        }
                    }, 50);
                });

                // Render first load
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', renderDashCharts);
                } else {
                    renderDashCharts();
                }

            })();
        </script>
    @endpush
</x-filament-panels::page>