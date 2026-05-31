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

    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

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

    <style>
        .dash {
            --gold: #B8956F;
            --gold-dark: #A07E5A;
            --gold-soft: #FAF8F5;
            --gold-tile: #F2EFE9;
            --charcoal: #2C2622;
            --espresso: #3B3529;
            --mocha: #8C7A63;
            --stone: #E8E0D8;
            --text: #1A1A1A;
            --muted: #64748B;
            --border: #E5E7EB;

            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .dark .dash { --text: #F9FAFB; --muted: #94A3B8; --border: #334155; }

        .dash * { box-sizing: border-box; }

        .dash-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        .dark .dash-card { background: #0F172A; }

        .dash-card-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
        }
        .dash-card-sub {
            font-size: 0.875rem;
            color: var(--muted);
            margin: 0.25rem 0 1rem 0;
        }
        .dash-card-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .dash-card-header-row .dash-card-title { font-size: 1.125rem; }
        .dash-card-header-row .dash-card-sub { margin: 0.25rem 0 0 0; }

        /* ============ HEADER ============ */
        .dash-header {
            background: linear-gradient(135deg, #FFFFFF 0%, #FAF8F5 100%);
            border: 1px solid var(--stone);
            border-radius: 1.25rem;
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .dark .dash-header {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            border-color: #334155;
        }
        .dash-greet-label {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 0;
        }
        .dash-greet-name {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.375rem 0 0 0;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .dash-greet-sub {
            font-size: 1rem;
            color: var(--muted);
            margin: 0.375rem 0 0 0;
        }
        .dash-greet-date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding: 0.375rem 0.875rem;
            background: var(--gold-tile);
            color: var(--mocha);
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .dark .dash-greet-date { background: #334155; color: #CBD5E1; }
        .dash-greet-date svg { width: 0.875rem; height: 0.875rem; }

        .dash-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
        }
        .dash-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 150ms;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            font-family: inherit;
        }
        .dash-action-btn:hover { background: var(--gold-soft); border-color: var(--gold); color: var(--mocha); }
        .dash-action-btn.dash-action-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            border: 0;
            box-shadow: 0 4px 12px rgba(184,149,111,0.25);
        }
        .dash-action-btn.dash-action-primary:hover { background: var(--gold-dark); color: #fff; }
        .dash-action-btn svg { width: 1rem; height: 1rem; flex-shrink: 0; }

        /* ============ TOP ROW (Top Performance + Quick Stats) ============ */
        .dash-top-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1280px) {
            .dash-top-row { grid-template-columns: 7fr 3fr; }
        }

        /* Top Performance — responsive grid */
        .dash-top-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.875rem;
        }
        @media (min-width: 768px) {
            .dash-top-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1280px) {
            .dash-top-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .dash-top-card {
            background: var(--gold-soft);
            border: 1px solid var(--stone);
            border-radius: 0.75rem;
            padding: 1.25rem 1.375rem;
            min-width: 0;
            transition: transform 200ms, box-shadow 200ms, border-color 200ms;
        }
        .dash-top-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(184,149,111,0.15);
            border-color: var(--gold);
        }
        .dark .dash-top-card { background: #1E293B; border-color: #334155; }

        .dash-top-label {
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--gold);
            margin: 0;
        }
        .dash-top-name {
            font-size: 1.0625rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.625rem 0 0.5rem 0;
            line-height: 1.35;
            min-height: 2.85rem;
            /* 2-line clamp */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            overflow-wrap: anywhere;
            word-break: break-word;
            letter-spacing: -0.01em;
        }
        .dash-top-meta {
            font-size: 0.875rem;
            color: var(--muted);
            margin: 0;
            font-weight: 500;
        }
        .dash-top-meta strong {
            color: var(--gold);
            font-weight: 800;
        }

        /* Quick Stats responsive grid */
        .dash-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 1280px) {
            .dash-quick-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        .dash-quick {
            text-align: center;
            padding: 1.125rem 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            background: var(--gold-soft);
            min-width: 0;
            overflow: hidden;
            transition: transform 200ms, box-shadow 200ms;
        }
        .dash-quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(184,149,111,0.12);
        }
        .dark .dash-quick { background: #1E293B; border-color: #334155; }
        .dash-quick-num {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gold);
            margin: 0;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .dash-quick-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            margin: 0.5rem 0 0 0;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.3;
        }

        /* ============ STAT CARDS ============ */
        .dash-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) { .dash-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .dash-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .dash-stats-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); } }

        .dash-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: transform 200ms, box-shadow 200ms;
        }
        .dash-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
        .dark .dash-stat { background: #0F172A; }

        .dash-stat-gold {
            background: linear-gradient(135deg, #B8956F, #A07E5A);
            color: #fff;
            border: 0;
            box-shadow: 0 4px 16px rgba(184,149,111,0.3);
        }
        .dash-stat-gold .dash-stat-label,
        .dash-stat-gold .dash-stat-value { color: #fff; }

        .dash-stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin: 0;
        }
        .dash-stat-gold .dash-stat-label { color: rgba(255,255,255,0.85); }

        .dash-stat-value {
            font-size: 1.625rem;
            font-weight: 800;
            color: var(--text);
            margin: 0;
            line-height: 1.15;
            word-break: break-word;
            letter-spacing: -0.02em;
        }
        .dash-stat-unit {
            font-size: 0.875rem;
            color: #94A3B8;
            font-weight: 500;
            margin-left: 0.25rem;
        }
        .dash-stat-gold .dash-stat-unit { color: rgba(255,255,255,0.7); }

        /* ============ CHARTS ============ */
        .dash-chart-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .dash-chart-grid { grid-template-columns: 2fr 1fr; } }

        .dash-chart-canvas {
            position: relative;
            height: 260px;
        }

        /* ============ TABLES & LISTS ============ */
        .dash-row-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9375rem;
        }
        .dash-row-table thead th {
            text-align: left;
            padding: 0.75rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }
        .dash-row-table tbody td {
            padding: 0.875rem;
            border-bottom: 1px solid #F3F4F6;
            color: #334155;
            vertical-align: middle;
        }
        .dark .dash-row-table tbody td { border-bottom-color: #1E293B; color: #E2E8F0; }
        .dash-row-table tbody tr:last-child td { border-bottom: none; }

        .dash-time-chip {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            background: var(--gold-tile);
            color: var(--mocha);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            font-family: ui-monospace, monospace;
        }
        .dark .dash-time-chip { background: #334155; color: #CBD5E1; }

        /* Status badges */
        .dash-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .dash-status-diajukan { background: #FEF3C7; color: #B45309; }
        .dash-status-disetujui { background: #DBEAFE; color: #1D4ED8; }
        .dash-status-menunggu_pembayaran { background: #FEF3C7; color: #B45309; }
        .dash-status-selesai { background: #D1FAE5; color: #047857; }
        .dash-status-batal,
        .dash-status-tidak_hadir { background: #FEE2E2; color: #B91C1C; }
        .dash-status-default { background: #F3F4F6; color: #6B7280; }

        .dash-pay-status-sudah_dibayar { background: #D1FAE5; color: #047857; }
        .dash-pay-status-menunggu_pembayaran { background: #FEF3C7; color: #B45309; }

        /* Recent appointment list */
        .dash-list { display: flex; flex-direction: column; gap: 0.625rem; }
        .dash-list-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 0.875rem;
            border-radius: 0.625rem;
            transition: background 150ms;
        }
        .dash-list-item:hover { background: var(--gold-soft); }
        .dark .dash-list-item:hover { background: #1E293B; }
        .dash-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9375rem;
            flex-shrink: 0;
        }
        .dash-list-body { flex: 1; min-width: 0; }
        .dash-list-name {
            font-weight: 700;
            color: var(--text);
            margin: 0;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dash-list-code {
            display: inline-block;
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gold);
            background: var(--gold-tile);
            padding: 0.125rem 0.5rem;
            border-radius: 0.375rem;
            letter-spacing: 0;
        }
        .dark .dash-list-code { background: #334155; color: #CBD5E1; }
        .dash-list-sub { font-size: 0.8125rem; color: var(--muted); margin: 0.125rem 0 0 0; }
        .dash-list-meta { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.375rem; }
        .dash-list-time { font-size: 0.75rem; color: #94A3B8; flex-shrink: 0; white-space: nowrap; }

        /* ============ ACTIVITY FEED ============ */
        .dash-feed { display: flex; flex-direction: column; gap: 1rem; }
        .dash-feed-item { display: flex; gap: 0.875rem; }
        .dash-feed-dot {
            flex-shrink: 0;
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 9999px;
            margin-top: 0.5rem;
        }
        .dash-feed-dot-emerald { background: #10B981; }
        .dash-feed-dot-blue { background: #3B82F6; }
        .dash-feed-dot-amber { background: #F59E0B; }
        .dash-feed-dot-purple { background: #A855F7; }
        .dash-feed-dot-rose { background: #F43F5E; }
        .dash-feed-body { flex: 1; min-width: 0; }
        .dash-feed-title { font-weight: 700; color: var(--text); margin: 0; font-size: 0.9375rem; }
        .dash-feed-desc { font-size: 0.8125rem; color: var(--muted); margin: 0.25rem 0 0 0; word-break: break-word; }
        .dash-feed-time { font-size: 0.75rem; color: #94A3B8; margin: 0.25rem 0 0 0; }

        /* ============ REMINDERS ============ */
        .dash-reminders-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .dash-alert {
            display: flex;
            gap: 0.875rem;
            padding: 1rem;
            border-radius: 0.75rem;
            border-left: 3px solid;
        }
        .dash-alert-amber { background: #FFFBEB; border-color: #F59E0B; }
        .dash-alert-rose { background: #FFF1F2; border-color: #F43F5E; }
        .dash-alert-blue { background: #EFF6FF; border-color: #3B82F6; }
        .dark .dash-alert-amber { background: rgba(180, 83, 9, 0.1); }
        .dark .dash-alert-rose { background: rgba(190, 18, 60, 0.1); }
        .dark .dash-alert-blue { background: rgba(29, 78, 216, 0.1); }

        .dash-alert-body { flex: 1; min-width: 0; }
        .dash-alert-title { font-size: 0.9375rem; font-weight: 700; color: var(--text); margin: 0; }
        .dash-alert-desc { font-size: 0.8125rem; color: var(--muted); margin: 0.25rem 0 0 0; }

        /* ============ EMPTY STATE ============ */
        .dash-empty {
            text-align: center;
            padding: 1.75rem 1rem;
            color: var(--muted);
            font-size: 0.875rem;
            font-style: italic;
        }

        /* ============ LAYOUT ROWS ============ */
        .dash-row-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .dash-row-2 { grid-template-columns: 1.5fr 1fr; } }

        .dash-link-all {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--gold);
            text-decoration: none;
            transition: color 150ms;
        }
        .dash-link-all:hover { color: var(--gold-dark); }
        .dash-link-all svg { width: 0.875rem; height: 0.875rem; }
    </style>

    <div class="dash">

        {{-- ============ 1. HEADER ============ --}}
        <div class="dash-header">
            <div>
                <p class="dash-greet-label">Dashboard Overview</p>
                <h1 class="dash-greet-name">{{ $data['greeting'] }}, {{ $data['adminName'] }} 👋</h1>
                <p class="dash-greet-sub">Ringkasan aktivitas dan operasional klinik hari ini.</p>
                <span class="dash-greet-date">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $data['today'] }}
                </span>
            </div>

            <div class="dash-actions">
                <a href="{{ $apptUrl }}" class="dash-action-btn dash-action-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Booking
                </a>
                <a href="{{ $patientUrl }}" class="dash-action-btn">Pasien</a>
                <a href="{{ $productUrl }}" class="dash-action-btn">Produk</a>
                <a href="{{ $treatmentUrl }}" class="dash-action-btn">Treatment</a>
            </div>
        </div>

        {{-- ============ 2. TOP PERFORMANCE + QUICK STATS ============ --}}
        <div class="dash-top-row">
            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Top Performance</h3>
                        <p class="dash-card-sub">Performa terbaik dalam 30 hari terakhir</p>
                    </div>
                </div>
                <div class="dash-top-grid">
                    <div class="dash-top-card">
                        <p class="dash-top-label">Treatment Terlaris</p>
                        <p class="dash-top-name" title="{{ $topPerformance['treatment']['name'] ?? '' }}">
                            {{ $topPerformance['treatment']['name'] ?? '—' }}
                        </p>
                        <p class="dash-top-meta"><strong>{{ number_format($topPerformance['treatment']['qty'] ?? 0, 0, ',', '.') }}</strong> sesi</p>
                    </div>
                    <div class="dash-top-card">
                        <p class="dash-top-label">Produk Terlaris</p>
                        <p class="dash-top-name" title="{{ $topPerformance['product']['name'] ?? '' }}">
                            {{ $topPerformance['product']['name'] ?? '—' }}
                        </p>
                        <p class="dash-top-meta"><strong>{{ number_format($topPerformance['product']['qty'] ?? 0, 0, ',', '.') }}</strong> pcs</p>
                    </div>
                    <div class="dash-top-card">
                        <p class="dash-top-label">Dokter Teraktif</p>
                        <p class="dash-top-name" title="{{ $topPerformance['doctor']['name'] ?? '' }}">
                            {{ $topPerformance['doctor']['name'] ?? '—' }}
                        </p>
                        <p class="dash-top-meta"><strong>{{ number_format($topPerformance['doctor']['qty'] ?? 0, 0, ',', '.') }}</strong> layanan</p>
                    </div>
                    <div class="dash-top-card">
                        <p class="dash-top-label">Terapis Teraktif</p>
                        <p class="dash-top-name" title="{{ $topPerformance['terapis']['name'] ?? '' }}">
                            {{ $topPerformance['terapis']['name'] ?? '—' }}
                        </p>
                        <p class="dash-top-meta"><strong>{{ number_format($topPerformance['terapis']['qty'] ?? 0, 0, ',', '.') }}</strong> sesi</p>
                    </div>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Quick Stats</h3>
                        <p class="dash-card-sub">Total master data klinik</p>
                    </div>
                </div>
                <div class="dash-quick-grid">
                    <div class="dash-quick">
                        <p class="dash-quick-num">{{ number_format($quickStats['total_pasien'], 0, ',', '.') }}</p>
                        <p class="dash-quick-label">Pasien</p>
                    </div>
                    <div class="dash-quick">
                        <p class="dash-quick-num">{{ number_format($quickStats['total_produk'], 0, ',', '.') }}</p>
                        <p class="dash-quick-label">Produk</p>
                    </div>
                    <div class="dash-quick">
                        <p class="dash-quick-num">{{ number_format($quickStats['total_treatment'], 0, ',', '.') }}</p>
                        <p class="dash-quick-label">Treatment</p>
                    </div>
                    <div class="dash-quick">
                        <p class="dash-quick-num">{{ number_format($quickStats['total_dokter'], 0, ',', '.') }}</p>
                        <p class="dash-quick-label">Dokter</p>
                    </div>
                    <div class="dash-quick">
                        <p class="dash-quick-num">{{ number_format($quickStats['total_terapis'], 0, ',', '.') }}</p>
                        <p class="dash-quick-label">Terapis</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ 3. SUMMARY CARDS ============ --}}
        <div class="dash-stats-grid">
            <div class="dash-stat dash-stat-gold">
                <p class="dash-stat-label">Pendapatan Hari Ini</p>
                <p class="dash-stat-value">{{ $rupiah($stats['revenue_today']) }}</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Janji Temu</p>
                <p class="dash-stat-value">{{ $stats['appointment_today'] }}<span class="dash-stat-unit">temu</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Pasien Hari Ini</p>
                <p class="dash-stat-value">{{ $stats['patient_today'] }}<span class="dash-stat-unit">pasien</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Produk Terjual</p>
                <p class="dash-stat-value">{{ $stats['product_sold_today'] }}<span class="dash-stat-unit">pcs</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Treatment Selesai</p>
                <p class="dash-stat-value">{{ $stats['treatment_done_today'] }}<span class="dash-stat-unit">sesi</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Pending Payment</p>
                <p class="dash-stat-value">{{ $stats['pending_payment'] }}<span class="dash-stat-unit">tx</span></p>
            </div>
        </div>

        {{-- ============ 4. CHARTS ============ --}}
        <div class="dash-chart-grid">
            <div class="dash-card">
                <h3 class="dash-card-title">Pendapatan 7 Hari Terakhir</h3>
                <p class="dash-card-sub">Trend pemasukan harian klinik</p>
                <div class="dash-chart-canvas">
                    <canvas id="dash-chart-revenue"
                            data-labels='@json($chartRevenue['labels'])'
                            data-values='@json($chartRevenue['data'])'></canvas>
                </div>
            </div>

            <div class="dash-card">
                <h3 class="dash-card-title">Komposisi Pendapatan</h3>
                <p class="dash-card-sub">Breakdown 30 hari terakhir</p>
                <div class="dash-chart-canvas">
                    <canvas id="dash-chart-composition"
                            data-labels='@json($chartComposition['labels'])'
                            data-values='@json($chartComposition['data'])'></canvas>
                </div>
            </div>
        </div>

        <div class="dash-card">
            <h3 class="dash-card-title">Appointment 7 Hari Terakhir</h3>
            <p class="dash-card-sub">Volume janji temu harian</p>
            <div class="dash-chart-canvas">
                <canvas id="dash-chart-appointments"
                        data-labels='@json($chartAppointments['labels'])'
                        data-values='@json($chartAppointments['data'])'></canvas>
            </div>
        </div>

        {{-- ============ 5. JADWAL HARI INI + APPOINTMENT TERBARU ============ --}}
        <div class="dash-row-2">
            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Jadwal Hari Ini</h3>
                        <p class="dash-card-sub">{{ count($todaySchedule) }} appointment terjadwal</p>
                    </div>
                    <a href="{{ $allScheduleUrl }}" class="dash-link-all">
                        Lihat Semua
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if(empty($todaySchedule))
                    <div class="dash-empty">Belum ada jadwal untuk hari ini.</div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="dash-row-table">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>Pasien</th>
                                    <th>Layanan</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedule as $s)
                                    <tr>
                                        <td><span class="dash-time-chip">{{ $s['time'] }}</span></td>
                                        <td style="font-weight:600;color:var(--text);">{{ $s['pasien'] }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($s['service'], 30) }}</td>
                                        <td style="color:var(--muted);">{{ $s['petugas'] }}</td>
                                        <td>
                                            <span class="dash-status dash-status-{{ in_array($s['status'], ['diajukan','disetujui','menunggu_pembayaran','selesai','batal','tidak_hadir']) ? $s['status'] : 'default' }}">
                                                {{ $s['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Appointment Terbaru</h3>
                        <p class="dash-card-sub">5 booking terkini</p>
                    </div>
                </div>
                @if(empty($recentAppointments))
                    <div class="dash-empty">Belum ada appointment.</div>
                @else
                    <div class="dash-list">
                        @foreach($recentAppointments as $a)
                            <div class="dash-list-item">
                                <div class="dash-avatar">{{ $a['initial'] }}</div>
                                <div class="dash-list-body">
                                    <p class="dash-list-name">
                                        {{ $a['pasien'] }}
                                        <span class="dash-list-code">{{ $a['kode_appointment'] }}</span>
                                    </p>
                                    <p class="dash-list-sub">{{ \Illuminate\Support\Str::limit($a['service'], 28) }}</p>
                                    <div class="dash-list-meta">
                                        <span class="dash-status dash-status-{{ in_array($a['status'], ['diajukan','disetujui','menunggu_pembayaran','selesai','batal','tidak_hadir']) ? $a['status'] : 'default' }}">
                                            {{ $a['status_label'] }}
                                        </span>
                                        <span class="dash-status dash-pay-status-{{ in_array($a['payment_status'], ['sudah_dibayar','menunggu_pembayaran']) ? $a['payment_status'] : 'default' }}">
                                            {{ $a['payment_label'] }}
                                        </span>
                                    </div>
                                </div>
                                <span class="dash-list-time">{{ $a['booking_time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ 6. AKTIVITAS TERBARU + REMINDER ============ --}}
        <div class="dash-row-2">
            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Aktivitas Terbaru</h3>
                        <p class="dash-card-sub">Aktivitas klinik dalam waktu dekat</p>
                    </div>
                </div>
                @if(empty($recentActivities))
                    <div class="dash-empty">Belum ada aktivitas terbaru.</div>
                @else
                    <div class="dash-feed">
                        @foreach($recentActivities as $act)
                            <div class="dash-feed-item">
                                <div class="dash-feed-dot dash-feed-dot-{{ $act['icon_color'] }}"></div>
                                <div class="dash-feed-body">
                                    <p class="dash-feed-title">{{ $act['title'] }}</p>
                                    <p class="dash-feed-desc">{{ $act['desc'] }}</p>
                                    <p class="dash-feed-time">{{ $act['time'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="dash-card">
                <div class="dash-card-header-row">
                    <div>
                        <h3 class="dash-card-title">Pengingat</h3>
                        <p class="dash-card-sub">Hal yang perlu perhatian admin</p>
                    </div>
                </div>
                @if(empty($reminders))
                    <div class="dash-empty">✓ Tidak ada pengingat — semua aman.</div>
                @else
                    <div class="dash-reminders-grid">
                        @foreach($reminders as $r)
                            <div class="dash-alert dash-alert-{{ $r['severity'] }}">
                                <div class="dash-alert-body">
                                    <p class="dash-alert-title">{{ $r['title'] }}</p>
                                    <p class="dash-alert-desc">{{ $r['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
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
                        const existing = Chart.getChart(c);
                        if (existing) existing.destroy();
                    });

                    // Default font/size for all charts
                    Chart.defaults.font.family = FONT_FAMILY;
                    Chart.defaults.font.size = 12;

                    const goldGradient = (ctx, h = 260) => {
                        const g = ctx.createLinearGradient(0, 0, 0, h);
                        g.addColorStop(0, 'rgba(184,149,111,0.8)');
                        g.addColorStop(1, 'rgba(184,149,111,0.05)');
                        return g;
                    };

                    // Chart 1: Revenue 7 hari (line)
                    const cv1 = document.getElementById('dash-chart-revenue');
                    if (cv1) {
                        const labels = JSON.parse(cv1.dataset.labels || '[]');
                        const values = JSON.parse(cv1.dataset.values || '[]');
                        new Chart(cv1, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Pendapatan',
                                    data: values,
                                    borderColor: '#B8956F',
                                    backgroundColor: (c) => goldGradient(c.chart.ctx),
                                    borderWidth: 2.5,
                                    tension: 0.35,
                                    fill: true,
                                    pointBackgroundColor: '#B8956F',
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: (ctx) => rupiah(ctx.parsed.y) } },
                                },
                                scales: {
                                    y: { ticks: { callback: (v) => rupiah(v), font: { size: 12 } } },
                                    x: { ticks: { font: { size: 12 } } },
                                },
                            },
                        });
                    }

                    // Chart 2: Komposisi (doughnut)
                    const cv2 = document.getElementById('dash-chart-composition');
                    if (cv2) {
                        const labels = JSON.parse(cv2.dataset.labels || '[]');
                        const values = JSON.parse(cv2.dataset.values || '[]');
                        const hasData = values.some(v => v > 0);
                        new Chart(cv2, {
                            type: 'doughnut',
                            data: {
                                labels: hasData ? labels : ['Belum ada data'],
                                datasets: [{
                                    data: hasData ? values : [1],
                                    backgroundColor: hasData ? palette : ['#E5E7EB'],
                                    borderWidth: 2,
                                    borderColor: '#fff',
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { font: { size: 13 }, boxWidth: 14, padding: 12 } },
                                    tooltip: {
                                        enabled: hasData,
                                        callbacks: { label: (ctx) => `${ctx.label}: ${rupiah(ctx.parsed)}` },
                                    },
                                },
                            },
                        });
                    }

                    // Chart 3: Appointment 7 hari (bar)
                    const cv3 = document.getElementById('dash-chart-appointments');
                    if (cv3) {
                        const labels = JSON.parse(cv3.dataset.labels || '[]');
                        const values = JSON.parse(cv3.dataset.values || '[]');
                        new Chart(cv3, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Appointment',
                                    data: values,
                                    backgroundColor: '#3B3529',
                                    borderRadius: 8,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { ticks: { font: { size: 12 } }, beginAtZero: true },
                                    x: { ticks: { font: { size: 12 } } },
                                },
                            },
                        });
                    }
                }

                document.addEventListener('DOMContentLoaded', renderDashCharts);
                document.addEventListener('livewire:navigated', renderDashCharts);
                if (window.Livewire) {
                    window.Livewire.hook('morph.updated', renderDashCharts);
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
