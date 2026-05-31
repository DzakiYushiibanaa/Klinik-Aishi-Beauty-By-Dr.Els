@php
    $data = $this->viewData;
    $summary = $data['summary'];
    $rows = $data['rows'];
    $totalRows = $data['totalRows'];
    $totalPages = $data['totalPages'];
    $tableTotals = $data['tableTotals'];
    $weeks = $data['weeks'];
    $chartTopTherapists = $data['chartTopTherapists'];
    $chartRevenue = $data['chartRevenue'];
    $chartCommission = $data['chartCommission'];
    $periodLabel = $data['periodLabel'];

    $monthOptions = $data['monthOptions'];
    $yearOptions = $data['yearOptions'];
    $weekOptions = $data['weekOptions'];
    $therapistOptions = $data['therapistOptions'];
    $statusOptions = $data['statusOptions'];

    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $loadingTargets = 'filter_month,filter_year,filter_week,filter_terapis_user_id,filter_status,filter_search,sortBy,gotoPage,nextPage,previousPage,per_page,resetFilters';
@endphp

<x-filament-panels::page>

@include('filament.partials.report-loading-styles')

    @push('scripts')
        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endonce
    @endpush

    <style>
        .tpr {
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
        .dark .tpr { --text: #F9FAFB; --muted: #94A3B8; --border: #334155; }

        .tpr * { box-sizing: border-box; }

        .tpr-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.25rem;
        }
        .dark .tpr-card { background: #0F172A; }

        .tpr-card-flush { padding: 0; overflow: hidden; }

        .tpr-card h3.tpr-card-title { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text); }
        .tpr-card .tpr-card-sub { font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem; }
        .tpr-card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }

        /* FILTER */
        .tpr-filter-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .tpr-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .tpr-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

        .tpr-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }
        .dark .tpr-label { color: #CBD5E1; }

        .tpr-input,
        .tpr-select {
            width: 100%;
            display: block;
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: var(--text);
            background: #fff;
            transition: border-color 150ms, box-shadow 150ms;
            font-family: inherit;
        }
        .dark .tpr-input, .dark .tpr-select { background: #1E293B; border-color: #475569; color: #F9FAFB; }
        .tpr-input:focus, .tpr-select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184,149,111,0.25);
        }
        .tpr-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.625rem center;
            background-size: 1.125rem;
            padding-right: 2rem;
        }

        /* BUTTON */
        .tpr-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            border: 0;
            transition: background 150ms, opacity 150ms;
            text-decoration: none;
            font-family: inherit;
        }
        .tpr-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .tpr-btn-outline { background: #fff; color: #475569; border: 1px solid var(--border); }
        .tpr-btn-outline:hover:not(:disabled) { background: #F9FAFB; }
        .dark .tpr-btn-outline { background: #1E293B; color: #CBD5E1; }
        .tpr-btn-emerald { background: #059669; color: #fff; }
        .tpr-btn-emerald:hover:not(:disabled) { background: #047857; }
        .tpr-btn-blue { background: #2563EB; color: #fff; }
        .tpr-btn-blue:hover:not(:disabled) { background: #1D4ED8; }
        .tpr-btn-rose { background: #E11D48; color: #fff; }
        .tpr-btn-rose:hover:not(:disabled) { background: #BE123C; }
        .tpr-btn-charcoal { background: var(--charcoal); color: #fff; }
        .tpr-btn-charcoal:hover:not(:disabled) { background: #1A1A1A; }
        .tpr-btn-icon { width: 0.875rem; height: 0.875rem; stroke-width: 2; flex-shrink: 0; }

        /* SUMMARY */
        .tpr-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) { .tpr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .tpr-summary-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

        .tpr-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            transition: transform 200ms, box-shadow 200ms;
        }
        .tpr-stat:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
        .dark .tpr-stat { background: #0F172A; }

        .tpr-stat-gold {
            background: linear-gradient(135deg, #B8956F, #A07E5A);
            color: #fff;
            border: 0;
            box-shadow: 0 4px 12px rgba(184,149,111,0.25);
        }
        .tpr-stat-gold .tpr-stat-label, .tpr-stat-gold .tpr-stat-value { color: #fff; }

        .tpr-stat-label {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748B;
            margin: 0;
        }
        .tpr-stat-gold .tpr-stat-label { color: rgba(255,255,255,0.85); }

        .tpr-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.5rem 0 0 0;
            line-height: 1.2;
        }
        .tpr-stat-value.tpr-stat-md { font-size: 1.125rem; }

        .tpr-stat-meta { font-size: 0.75rem; color: var(--muted); margin: 0.25rem 0 0 0; }

        .tpr-stat-icon {
            flex-shrink: 0;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tpr-stat-icon svg { width: 1.125rem; height: 1.125rem; }
        .tpr-stat-icon-emerald { background: #D1FAE5; color: #047857; }
        .tpr-stat-icon-amber { background: #FEF3C7; color: #B45309; }
        .tpr-stat-icon-blue { background: #DBEAFE; color: #1D4ED8; }
        .tpr-stat-icon-purple { background: #EDE9FE; color: #6D28D9; }
        .tpr-stat-icon-white { background: rgba(255,255,255,0.2); color: #fff; }

        /* EXPORT */
        .tpr-export { display: flex; flex-direction: column; gap: 0.75rem; }
        @media (min-width: 640px) {
            .tpr-export { flex-direction: row; align-items: center; justify-content: space-between; }
        }
        .tpr-export-buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .tpr-loading-text { margin-top: 0.75rem; font-size: 0.75rem; color: var(--muted); font-style: italic; }

        /* CHART */
        .tpr-chart-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .tpr-chart-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .tpr-chart-canvas { position: relative; height: 240px; margin-top: 0.5rem; }

        /* WEEKLY */
        .tpr-weekly-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .tpr-weekly-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .tpr-weekly-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .tpr-weekly-card {
            background: var(--gold-soft);
            border: 1px solid var(--stone);
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .dark .tpr-weekly-card { background: #1E293B; border-color: #334155; }

        .tpr-weekly-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .tpr-weekly-label { font-size: 0.75rem; font-weight: 700; color: var(--gold); letter-spacing: 0.03em; }
        .tpr-weekly-range { font-size: 0.625rem; color: var(--muted); }
        .tpr-weekly-revenue { font-size: 1.125rem; font-weight: 800; color: var(--text); margin: 0.5rem 0 0 0; }
        .tpr-weekly-qty { font-size: 0.75rem; color: var(--muted); margin: 0.125rem 0 0 0; }
        .tpr-weekly-commission {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.125rem 0.5rem;
            background: #ECFDF5;
            color: #047857;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .dark .tpr-weekly-commission { background: #064E3B; color: #6EE7B7; }
        .tpr-weekly-foot { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
        .tpr-weekly-foot-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94A3B8; margin: 0; }
        .tpr-weekly-foot-name { font-size: 0.75rem; font-weight: 700; color: #334155; margin: 0.125rem 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .tpr-weekly-foot-name { color: #E2E8F0; }
        .tpr-weekly-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.875rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tpr-weekly-total-label { font-weight: 700; color: var(--text); }
        .tpr-weekly-total-value { font-weight: 800; color: var(--gold); }

        /* TABLE */
        .tpr-table-wrap { overflow-x: auto; }
        .tpr-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .tpr-table thead {
            background: var(--charcoal);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .tpr-table thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            white-space: nowrap;
        }
        .tpr-table thead th.tpr-sortable { cursor: pointer; user-select: none; }
        .tpr-table thead th.tpr-sortable:hover { background: #1A1A1A; }
        .tpr-th-content { display: inline-flex; align-items: center; gap: 0.25rem; }
        .tpr-table thead th.tpr-text-right .tpr-th-content { float: right; }
        .tpr-table thead th.tpr-text-center .tpr-th-content { display: flex; justify-content: center; }

        .tpr-table tbody tr { border-top: 1px solid #F3F4F6; transition: background 150ms; }
        .dark .tpr-table tbody tr { border-top-color: #1E293B; }
        .tpr-table tbody tr:hover { background: var(--gold-soft); }
        .dark .tpr-table tbody tr:hover { background: #1E293B; }

        .tpr-table td { padding: 0.75rem 1rem; color: #334155; vertical-align: middle; }
        .dark .tpr-table td { color: #E2E8F0; }
        .tpr-text-right { text-align: right; }
        .tpr-text-center { text-align: center; }

        .tpr-no { color: var(--muted); font-size: 0.75rem; }
        .tpr-name { font-weight: 700; color: var(--text); margin: 0; }
        .tpr-name-sub { font-size: 0.6875rem; color: var(--muted); font-weight: 500; }
        .tpr-rate-chip {
            display: inline-block;
            background: #DBEAFE;
            color: #1D4ED8;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .dark .tpr-rate-chip { background: #1E3A8A; color: #BFDBFE; }
        .tpr-fav-chip {
            display: inline-block;
            background: var(--gold-tile);
            color: var(--mocha);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .dark .tpr-fav-chip { background: #334155; color: #CBD5E1; }
        .tpr-status-aktif {
            display: inline-block;
            background: #D1FAE5;
            color: #047857;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .tpr-status-nonaktif {
            display: inline-block;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .tpr-qty { font-weight: 700; color: var(--text); }
        .tpr-revenue { font-weight: 800; color: var(--gold); }
        .tpr-commission { font-weight: 800; color: #047857; }
        .tpr-meta-cell { font-size: 0.75rem; color: var(--muted); }

        .tpr-table tfoot { background: var(--gold-soft); border-top: 2px solid rgba(184,149,111,0.4); }
        .dark .tpr-table tfoot { background: #1E293B; }
        .tpr-table tfoot td { font-weight: 800; font-size: 0.875rem; }

        .tpr-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .tpr-empty svg { width: 2.5rem; height: 2.5rem; color: #CBD5E1; }
        .tpr-empty-title { font-weight: 700; color: var(--text); font-size: 0.875rem; margin: 0; }
        .tpr-empty-sub { font-size: 0.75rem; margin: 0; }

        .tpr-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tpr-page-list { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .tpr-page-btn {
            border: 1px solid var(--border);
            background: #fff;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 150ms;
            font-family: inherit;
        }
        .tpr-page-btn:hover:not(:disabled) { background: #F9FAFB; }
        .tpr-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .tpr-page-btn.tpr-active { background: var(--gold); color: #fff; border-color: var(--gold); }
        .dark .tpr-page-btn { background: #1E293B; color: #CBD5E1; border-color: #334155; }

        @media print {
            body { background: #fff !important; }
            .tpr-no-print { display: none !important; }
            .fi-sidebar, .fi-topbar, aside, header, nav { display: none !important; }
            .tpr-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>

    <div class="tpr">

        {{-- ============================ FILTERS ============================ --}}
        <div class="tpr-card tpr-no-print report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memperbarui data..." />
            <div class="tpr-card-header">
                <div>
                    <h3 class="tpr-card-title">Filter Laporan</h3>
                    <p class="tpr-card-sub">Periode aktif: <strong style="color: var(--gold)">{{ $periodLabel }}</strong></p>
                </div>
                <div style="display:flex;align-items:center;gap:0.625rem;">
                    <span wire:loading.remove wire:target="{{ $loadingTargets }}"
                          class="report-filter-status report-filter-status-idle">
                        <span class="report-filter-status-dot"></span>
                        Data terkini
                    </span>
                    <span wire:loading.flex wire:target="{{ $loadingTargets }}"
                          class="report-filter-status report-filter-status-loading">
                        <span class="report-filter-status-dot"></span>
                        Memperbarui data...
                    </span>
                    <button type="button" wire:click="resetFilters" class="tpr-btn tpr-btn-outline">
                        <svg class="tpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>

            <div class="tpr-filter-grid">
                <div>
                    <label class="tpr-label">Bulan</label>
                    <select wire:model.live="filter_month" class="tpr-select">
                        @foreach($monthOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="tpr-label">Tahun</label>
                    <select wire:model.live="filter_year" class="tpr-select">
                        @foreach($yearOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="tpr-label">Minggu</label>
                    <select wire:model.live="filter_week" class="tpr-select">
                        @foreach($weekOptions as $val => $label)
                            <option value="{{ $val === '' ? '' : $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="tpr-label">Nama Terapis</label>
                    <select wire:model.live="filter_terapis_user_id" class="tpr-select">
                        <option value="">Semua Terapis</option>
                        @foreach($therapistOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="tpr-label">Status Terapis</label>
                    <select wire:model.live="filter_status" class="tpr-select">
                        @foreach($statusOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="tpr-label">Cari</label>
                    <input type="search"
                           wire:model.live.debounce.500ms="filter_search"
                           placeholder="Terapis / treatment / kode order"
                           class="tpr-input">
                </div>
            </div>
        </div>

        {{-- ============================ SUMMARY CARDS ============================ --}}
        <div class="tpr-summary-grid">
            <div class="tpr-stat">
                <div style="flex:1;">
                    <p class="tpr-stat-label">Terapis Aktif</p>
                    <p class="tpr-stat-value">{{ number_format($summary['total_active'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">orang</span></p>
                </div>
                <div class="tpr-stat-icon tpr-stat-icon-emerald">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>

            <div class="tpr-stat">
                <div style="flex:1;">
                    <p class="tpr-stat-label">Treatment Ditangani</p>
                    <p class="tpr-stat-value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">sesi</span></p>
                </div>
                <div class="tpr-stat-icon tpr-stat-icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>

            <div class="tpr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="tpr-stat-label">Terapis Terproduktif</p>
                    <p class="tpr-stat-value tpr-stat-md" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $summary['best_therapist']['name'] ?? '' }}">
                        {{ $summary['best_therapist']['name'] ?? '—' }}
                    </p>
                    <p class="tpr-stat-meta">{{ $summary['best_therapist'] ? number_format($summary['best_therapist']['count'], 0, ',', '.') . ' sesi' : 'Belum ada data' }}</p>
                </div>
                <div class="tpr-stat-icon tpr-stat-icon-amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>

            <div class="tpr-stat tpr-stat-gold">
                <div style="flex:1;">
                    <p class="tpr-stat-label">Total Pendapatan</p>
                    <p class="tpr-stat-value">{{ $rupiah($summary['total_revenue']) }}</p>
                </div>
                <div class="tpr-stat-icon tpr-stat-icon-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>

            <div class="tpr-stat">
                <div style="flex:1;">
                    <p class="tpr-stat-label">Total Komisi</p>
                    <p class="tpr-stat-value tpr-stat-md" style="color:#047857;">{{ $rupiah($summary['total_commission']) }}</p>
                </div>
                <div class="tpr-stat-icon tpr-stat-icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ============================ EXPORT BAR ============================ --}}
        <div class="tpr-card tpr-no-print">
            <div class="tpr-export">
                <div>
                    <p style="margin:0;font-weight:700;color:var(--text);font-size:0.875rem;">Ekspor Laporan</p>
                    <p style="margin:0.25rem 0 0 0;font-size:0.75rem;color:var(--muted);">Mengikuti filter aktif. Total {{ number_format($totalRows, 0, ',', '.') }} terapis pada periode ini.</p>
                </div>
                <div class="tpr-export-buttons">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="tpr-btn tpr-btn-emerald">
                        <span wire:loading.remove wire:target="exportExcel" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="tpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Excel
                        </span>
                        <span wire:loading.flex wire:target="exportExcel" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="tpr-btn tpr-btn-blue">
                        <span wire:loading.remove wire:target="exportCsv" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="tpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            CSV
                        </span>
                        <span wire:loading.flex wire:target="exportCsv" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportPdf" wire:loading.attr="disabled" wire:target="exportPdf" class="tpr-btn tpr-btn-rose">
                        <span wire:loading.remove wire:target="exportPdf" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="tpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </span>
                        <span wire:loading.flex wire:target="exportPdf" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button onclick="window.print()" class="tpr-btn tpr-btn-charcoal">
                        <svg class="tpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>
            <div wire:loading wire:target="exportExcel,exportCsv,exportPdf" class="tpr-loading-text">
                Memproses ekspor...
            </div>
        </div>

        {{-- ============================ CHARTS ============================ --}}
        <div class="tpr-chart-grid report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat grafik..." />
            <div class="tpr-card">
                <h3 class="tpr-card-title">Top 5 Terapis</h3>
                <p class="tpr-card-sub">Berdasarkan jumlah treatment</p>
                <div class="tpr-chart-canvas">
                    <canvas id="tpr-chart-top"
                            data-labels='@json($chartTopTherapists['labels'])'
                            data-values='@json($chartTopTherapists['data'])'></canvas>
                </div>
            </div>

            <div class="tpr-card">
                <h3 class="tpr-card-title">Pendapatan per Terapis</h3>
                <p class="tpr-card-sub">Top 8 berdasarkan revenue</p>
                <div class="tpr-chart-canvas">
                    <canvas id="tpr-chart-revenue"
                            data-labels='@json($chartRevenue['labels'])'
                            data-values='@json($chartRevenue['data'])'></canvas>
                </div>
            </div>

            <div class="tpr-card">
                <h3 class="tpr-card-title">Komisi per Terapis</h3>
                <p class="tpr-card-sub">Top 8 berdasarkan komisi</p>
                <div class="tpr-chart-canvas">
                    <canvas id="tpr-chart-commission"
                            data-labels='@json($chartCommission['labels'])'
                            data-values='@json($chartCommission['data'])'></canvas>
                </div>
            </div>
        </div>

        {{-- ============================ WEEKLY RECAP ============================ --}}
        <div class="tpr-card report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat rekap mingguan..." />
            <div class="tpr-card-header">
                <div>
                    <h3 class="tpr-card-title">Rekap Mingguan</h3>
                    <p class="tpr-card-sub">Pembagian 4 minggu dalam bulan {{ $monthOptions[$this->filter_month] ?? '' }} {{ $this->filter_year }}</p>
                </div>
            </div>
            <div class="tpr-weekly-grid">
                @foreach($weeks as $w)
                    <div class="tpr-weekly-card">
                        <div class="tpr-weekly-head">
                            <span class="tpr-weekly-label">{{ $w['label'] }}</span>
                            <span class="tpr-weekly-range">{{ $w['range'] }}</span>
                        </div>
                        <p class="tpr-weekly-revenue">{{ $rupiah($w['total_revenue']) }}</p>
                        <p class="tpr-weekly-qty">{{ number_format($w['total_qty'], 0, ',', '.') }} treatment selesai</p>
                        <span class="tpr-weekly-commission">Komisi {{ $rupiah($w['total_commission']) }}</span>
                        <div class="tpr-weekly-foot">
                            <p class="tpr-weekly-foot-label">Terapis Paling Aktif</p>
                            <p class="tpr-weekly-foot-name" title="{{ $w['best_therapist'] ?? '' }}">
                                {{ $w['best_therapist'] ?? '—' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="tpr-weekly-total">
                <span class="tpr-weekly-total-label">Total Bulanan</span>
                <span class="tpr-weekly-total-value">
                    {{ $rupiah($weeks->sum('total_revenue')) }} •
                    {{ number_format($weeks->sum('total_qty'), 0, ',', '.') }} sesi •
                    Komisi {{ $rupiah($weeks->sum('total_commission')) }}
                </span>
            </div>
        </div>

        {{-- ============================ TABLE ============================ --}}
        <div class="tpr-card tpr-card-flush report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat tabel laporan..." />
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;flex-wrap:wrap;">
                <div>
                    <h3 class="tpr-card-title">Detail Kinerja Terapis</h3>
                    <p class="tpr-card-sub">Total {{ number_format($totalRows, 0, ',', '.') }} terapis pada periode aktif</p>
                </div>
                <div class="tpr-no-print" style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-size:0.75rem;color:var(--muted);">Per halaman</label>
                    <select wire:model.live="per_page" class="tpr-select" style="width:auto;padding:0.25rem 2rem 0.25rem 0.625rem;font-size:0.75rem;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="tpr-table-wrap">
                <table class="tpr-table">
                    <thead>
                        <tr>
                            <th style="width:3rem;">No</th>
                            <th class="tpr-sortable" wire:click="sortBy('terapis_name')">
                                <span class="tpr-th-content">Nama Terapis @include('filament.pages.partials.sort-icon-therapist', ['field' => 'terapis_name'])</span>
                            </th>
                            <th class="tpr-text-center tpr-sortable" wire:click="sortBy('total_treatment')">
                                <span class="tpr-th-content">Total Treatment @include('filament.pages.partials.sort-icon-therapist', ['field' => 'total_treatment'])</span>
                            </th>
                            <th class="tpr-text-center tpr-sortable" wire:click="sortBy('total_pasien')">
                                <span class="tpr-th-content">Total Pasien @include('filament.pages.partials.sort-icon-therapist', ['field' => 'total_pasien'])</span>
                            </th>
                            <th>Treatment Terfavorit</th>
                            <th class="tpr-text-right tpr-sortable" wire:click="sortBy('total_revenue')">
                                <span class="tpr-th-content">Total Pendapatan @include('filament.pages.partials.sort-icon-therapist', ['field' => 'total_revenue'])</span>
                            </th>
                            <th class="tpr-text-center">Komisi (%)</th>
                            <th class="tpr-text-right tpr-sortable" wire:click="sortBy('total_commission')">
                                <span class="tpr-th-content">Total Komisi @include('filament.pages.partials.sort-icon-therapist', ['field' => 'total_commission'])</span>
                            </th>
                            <th>Minggu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $r)
                            <tr>
                                <td class="tpr-no">{{ ($this->page - 1) * $this->per_page + $i + 1 }}</td>
                                <td>
                                    <p class="tpr-name">{{ $r['terapis_name'] }}</p>
                                    <span class="tpr-name-sub">{{ $r['kode_terapis'] }}</span>
                                </td>
                                <td class="tpr-text-center tpr-qty">{{ number_format($r['total_treatment'], 0, ',', '.') }}</td>
                                <td class="tpr-text-center tpr-qty">{{ number_format($r['total_pasien'], 0, ',', '.') }}</td>
                                <td>
                                    @if($r['favorite_treatment'])
                                        <span class="tpr-fav-chip">{{ $r['favorite_treatment'] }}</span>
                                    @else
                                        <span class="tpr-meta-cell">—</span>
                                    @endif
                                </td>
                                <td class="tpr-text-right tpr-revenue">{{ $rupiah($r['total_revenue']) }}</td>
                                <td class="tpr-text-center">
                                    <span class="tpr-rate-chip">{{ $r['commission_rate'] }}%</span>
                                </td>
                                <td class="tpr-text-right tpr-commission">{{ $rupiah($r['total_commission']) }}</td>
                                <td class="tpr-meta-cell">{{ $r['weeks_label'] }}</td>
                                <td>
                                    @if($r['status_kerja'] === 'aktif')
                                        <span class="tpr-status-aktif">Aktif</span>
                                    @else
                                        <span class="tpr-status-nonaktif">{{ ucfirst($r['status_kerja']) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="tpr-empty">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <p class="tpr-empty-title">Belum ada data kinerja terapis pada periode ini.</p>
                                        <p class="tpr-empty-sub">Hanya treatment dengan status pembayaran sudah dibayar dan appointment selesai yang dihitung.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalRows > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2" class="tpr-text-right" style="color:#475569;">TOTAL KESELURUHAN</td>
                                <td class="tpr-text-center">{{ number_format($tableTotals['total_treatment'], 0, ',', '.') }}</td>
                                <td class="tpr-text-center">{{ number_format($tableTotals['total_pasien'], 0, ',', '.') }}</td>
                                <td>—</td>
                                <td class="tpr-text-right" style="color:var(--gold);">{{ $rupiah($tableTotals['total_revenue']) }}</td>
                                <td>—</td>
                                <td class="tpr-text-right" style="color:#047857;">{{ $rupiah($tableTotals['total_commission']) }}</td>
                                <td colspan="2" style="font-size:0.75rem;color:var(--muted);font-style:italic;font-weight:400;">{{ $tableTotals['therapist_count'] }} terapis</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($totalPages > 1)
                <div class="tpr-pagination tpr-no-print">
                    <p style="color:var(--muted);margin:0;">
                        Halaman <strong style="color:var(--text);">{{ $this->page }}</strong> dari <strong style="color:var(--text);">{{ $totalPages }}</strong>
                    </p>
                    <div class="tpr-page-list">
                        <button wire:click="previousPage" @disabled($this->page <= 1) class="tpr-page-btn">← Sebelumnya</button>
                        @for($p = max(1, $this->page - 2); $p <= min($totalPages, $this->page + 2); $p++)
                            <button wire:click="gotoPage({{ $p }})" class="tpr-page-btn {{ $p === $this->page ? 'tpr-active' : '' }}">{{ $p }}</button>
                        @endfor
                        <button wire:click="nextPage" @disabled($this->page >= $totalPages) class="tpr-page-btn">Selanjutnya →</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================ CHART INIT ============================ --}}
    @push('scripts')
        <script>
            (function () {
                function rupiah(n) {
                    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
                }

                function renderTprCharts() {
                    if (typeof Chart === 'undefined') return;

                    document.querySelectorAll('.tpr canvas').forEach(c => {
                        const existing = Chart.getChart(c);
                        if (existing) existing.destroy();
                    });

                    const cv1 = document.getElementById('tpr-chart-top');
                    if (cv1) {
                        const labels = JSON.parse(cv1.dataset.labels || '[]');
                        const values = JSON.parse(cv1.dataset.values || '[]');
                        new Chart(cv1, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Jumlah Treatment',
                                    data: values,
                                    backgroundColor: '#CFAF53',
                                    borderRadius: 8,
                                }],
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                            },
                        });
                    }

                    const cv2 = document.getElementById('tpr-chart-revenue');
                    if (cv2) {
                        const labels = JSON.parse(cv2.dataset.labels || '[]');
                        const values = JSON.parse(cv2.dataset.values || '[]');
                        new Chart(cv2, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Pendapatan',
                                    data: values,
                                    backgroundColor: '#B8956F',
                                    borderRadius: 8,
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
                                    y: { ticks: { callback: (v) => rupiah(v) } },
                                },
                            },
                        });
                    }

                    const cv3 = document.getElementById('tpr-chart-commission');
                    if (cv3) {
                        const labels = JSON.parse(cv3.dataset.labels || '[]');
                        const values = JSON.parse(cv3.dataset.values || '[]');
                        new Chart(cv3, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Komisi',
                                    data: values,
                                    backgroundColor: '#059669',
                                    borderRadius: 8,
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
                                    y: { ticks: { callback: (v) => rupiah(v) } },
                                },
                            },
                        });
                    }
                }

                document.addEventListener('DOMContentLoaded', renderTprCharts);
                document.addEventListener('livewire:navigated', renderTprCharts);
                if (window.Livewire) {
                    window.Livewire.hook('morph.updated', renderTprCharts);
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
