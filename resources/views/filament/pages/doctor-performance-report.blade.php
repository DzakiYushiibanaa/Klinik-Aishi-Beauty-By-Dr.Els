@php
    $data = $this->viewData;
    $summary = $data['summary'];
    $rows = $data['rows'];
    $totalRows = $data['totalRows'];
    $totalPages = $data['totalPages'];
    $tableTotals = $data['tableTotals'];
    $weeks = $data['weeks'];
    $chartTopDoctors = $data['chartTopDoctors'];
    $chartRevenue = $data['chartRevenue'];
    $chartCommission = $data['chartCommission'];
    $periodLabel = $data['periodLabel'];

    $monthOptions = $data['monthOptions'];
    $yearOptions = $data['yearOptions'];
    $weekOptions = $data['weekOptions'];
    $doctorOptions = $data['doctorOptions'];
    $statusOptions = $data['statusOptions'];

    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $loadingTargets = 'filter_month,filter_year,filter_week,filter_dokter_user_id,filter_status,filter_search,sortBy,gotoPage,nextPage,previousPage,per_page,resetFilters';
@endphp

<x-filament-panels::page>

@include('filament.partials.report-loading-styles')

    @push('scripts')
        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endonce
    @endpush

    <style>
        .dpr {
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
        .dark .dpr { --text: #F9FAFB; --muted: #94A3B8; --border: #334155; }

        .dpr * { box-sizing: border-box; }

        .dpr-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.25rem;
        }
        .dark .dpr-card { background: #0F172A; }
        .dpr-card-flush { padding: 0; overflow: hidden; }

        .dpr-card h3.dpr-card-title { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text); }
        .dpr-card .dpr-card-sub { font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem; }
        .dpr-card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }

        /* FILTER */
        .dpr-filter-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .dpr-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .dpr-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

        .dpr-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }
        .dark .dpr-label { color: #CBD5E1; }

        .dpr-input,
        .dpr-select {
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
        .dark .dpr-input, .dark .dpr-select { background: #1E293B; border-color: #475569; color: #F9FAFB; }
        .dpr-input:focus, .dpr-select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184,149,111,0.25);
        }
        .dpr-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.625rem center;
            background-size: 1.125rem;
            padding-right: 2rem;
        }

        /* BUTTON */
        .dpr-btn {
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
        .dpr-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .dpr-btn-outline { background: #fff; color: #475569; border: 1px solid var(--border); }
        .dpr-btn-outline:hover:not(:disabled) { background: #F9FAFB; }
        .dark .dpr-btn-outline { background: #1E293B; color: #CBD5E1; }
        .dpr-btn-emerald { background: #059669; color: #fff; }
        .dpr-btn-emerald:hover:not(:disabled) { background: #047857; }
        .dpr-btn-blue { background: #2563EB; color: #fff; }
        .dpr-btn-blue:hover:not(:disabled) { background: #1D4ED8; }
        .dpr-btn-rose { background: #E11D48; color: #fff; }
        .dpr-btn-rose:hover:not(:disabled) { background: #BE123C; }
        .dpr-btn-charcoal { background: var(--charcoal); color: #fff; }
        .dpr-btn-charcoal:hover:not(:disabled) { background: #1A1A1A; }
        .dpr-btn-icon { width: 0.875rem; height: 0.875rem; stroke-width: 2; flex-shrink: 0; }

        /* SUMMARY */
        .dpr-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) { .dpr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .dpr-summary-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

        .dpr-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            transition: transform 200ms, box-shadow 200ms;
        }
        .dpr-stat:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
        .dark .dpr-stat { background: #0F172A; }

        .dpr-stat-gold {
            background: linear-gradient(135deg, #B8956F, #A07E5A);
            color: #fff;
            border: 0;
            box-shadow: 0 4px 12px rgba(184,149,111,0.25);
        }
        .dpr-stat-gold .dpr-stat-label, .dpr-stat-gold .dpr-stat-value { color: #fff; }

        .dpr-stat-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #64748B; margin: 0; }
        .dpr-stat-gold .dpr-stat-label { color: rgba(255,255,255,0.85); }

        .dpr-stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0.5rem 0 0 0; line-height: 1.2; }
        .dpr-stat-value.dpr-stat-md { font-size: 1.125rem; }
        .dpr-stat-meta { font-size: 0.75rem; color: var(--muted); margin: 0.25rem 0 0 0; }

        .dpr-stat-icon {
            flex-shrink: 0;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dpr-stat-icon svg { width: 1.125rem; height: 1.125rem; }
        .dpr-stat-icon-emerald { background: #D1FAE5; color: #047857; }
        .dpr-stat-icon-amber { background: #FEF3C7; color: #B45309; }
        .dpr-stat-icon-blue { background: #DBEAFE; color: #1D4ED8; }
        .dpr-stat-icon-purple { background: #EDE9FE; color: #6D28D9; }
        .dpr-stat-icon-rose { background: #FFE4E6; color: #BE123C; }
        .dpr-stat-icon-white { background: rgba(255,255,255,0.2); color: #fff; }

        /* EXPORT */
        .dpr-export { display: flex; flex-direction: column; gap: 0.75rem; }
        @media (min-width: 640px) {
            .dpr-export { flex-direction: row; align-items: center; justify-content: space-between; }
        }
        .dpr-export-buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .dpr-loading-text { margin-top: 0.75rem; font-size: 0.75rem; color: var(--muted); font-style: italic; }

        /* CHART */
        .dpr-chart-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .dpr-chart-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .dpr-chart-canvas { position: relative; height: 240px; margin-top: 0.5rem; }

        /* WEEKLY */
        .dpr-weekly-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .dpr-weekly-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .dpr-weekly-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .dpr-weekly-card {
            background: var(--gold-soft);
            border: 1px solid var(--stone);
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .dark .dpr-weekly-card { background: #1E293B; border-color: #334155; }

        .dpr-weekly-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .dpr-weekly-label { font-size: 0.75rem; font-weight: 700; color: var(--gold); letter-spacing: 0.03em; }
        .dpr-weekly-range { font-size: 0.625rem; color: var(--muted); }

        .dpr-weekly-stats {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin: 0.5rem 0;
        }
        .dpr-weekly-stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
        }
        .dpr-weekly-stat-row .label { color: var(--muted); }
        .dpr-weekly-stat-row .value { font-weight: 700; color: var(--text); }
        .dpr-weekly-revenue { font-size: 1rem; font-weight: 800; color: var(--gold); margin: 0.25rem 0 0 0; }
        .dpr-weekly-commission {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.125rem 0.5rem;
            background: #ECFDF5;
            color: #047857;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .dark .dpr-weekly-commission { background: #064E3B; color: #6EE7B7; }
        .dpr-weekly-foot { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
        .dpr-weekly-foot-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94A3B8; margin: 0; }
        .dpr-weekly-foot-name { font-size: 0.75rem; font-weight: 700; color: #334155; margin: 0.125rem 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .dpr-weekly-foot-name { color: #E2E8F0; }
        .dpr-weekly-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.875rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dpr-weekly-total-label { font-weight: 700; color: var(--text); }
        .dpr-weekly-total-value { font-weight: 800; color: var(--gold); }

        /* TABLE */
        .dpr-table-wrap { overflow-x: auto; }
        .dpr-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        .dpr-table thead {
            background: var(--charcoal);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .dpr-table thead th {
            text-align: left;
            padding: 0.75rem 0.875rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }
        .dpr-table thead th.dpr-sortable { cursor: pointer; user-select: none; }
        .dpr-table thead th.dpr-sortable:hover { background: #1A1A1A; }
        .dpr-th-content { display: inline-flex; align-items: center; gap: 0.25rem; }
        .dpr-table thead th.dpr-text-right .dpr-th-content { float: right; }
        .dpr-table thead th.dpr-text-center .dpr-th-content { display: flex; justify-content: center; }

        .dpr-table tbody tr { border-top: 1px solid #F3F4F6; transition: background 150ms; }
        .dark .dpr-table tbody tr { border-top-color: #1E293B; }
        .dpr-table tbody tr:hover { background: var(--gold-soft); }
        .dark .dpr-table tbody tr:hover { background: #1E293B; }

        .dpr-table td { padding: 0.75rem 0.875rem; color: #334155; vertical-align: top; }
        .dark .dpr-table td { color: #E2E8F0; }
        .dpr-text-right { text-align: right; }
        .dpr-text-center { text-align: center; }

        .dpr-no { color: var(--muted); font-size: 0.75rem; }
        .dpr-name { font-weight: 700; color: var(--text); margin: 0; }
        .dpr-name-sub { font-size: 0.6875rem; color: var(--muted); font-weight: 500; }

        .dpr-num { font-weight: 700; color: var(--text); }
        .dpr-num-konsultasi { font-weight: 700; color: #1D4ED8; }
        .dpr-num-treatment { font-weight: 700; color: var(--gold); }
        .dpr-revenue { font-weight: 800; color: var(--gold); }
        .dpr-commission { font-weight: 800; color: #047857; }
        .dpr-commission-secondary { font-size: 0.75rem; color: #047857; font-weight: 700; }

        .dpr-rate-chip {
            display: inline-block;
            background: #DBEAFE;
            color: #1D4ED8;
            font-size: 0.625rem;
            font-weight: 700;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            margin-left: 0.25rem;
        }
        .dpr-rate-chip-emerald {
            background: #D1FAE5;
            color: #047857;
        }
        .dark .dpr-rate-chip { background: #1E3A8A; color: #BFDBFE; }
        .dark .dpr-rate-chip-emerald { background: #064E3B; color: #6EE7B7; }

        .dpr-treatment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            max-width: 18rem;
        }
        .dpr-treatment-chip {
            display: inline-flex;
            align-items: center;
            background: var(--gold-tile);
            color: var(--mocha);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            white-space: nowrap;
        }
        .dpr-treatment-chip-more {
            background: #F3F4F6;
            color: #6B7280;
        }
        .dark .dpr-treatment-chip { background: #334155; color: #CBD5E1; }
        .dark .dpr-treatment-chip-more { background: #1E293B; color: #94A3B8; }

        .dpr-status-aktif {
            display: inline-block;
            background: #D1FAE5;
            color: #047857;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .dpr-status-nonaktif {
            display: inline-block;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .dpr-meta-cell { font-size: 0.75rem; color: var(--muted); }

        .dpr-table tfoot { background: var(--gold-soft); border-top: 2px solid rgba(184,149,111,0.4); }
        .dark .dpr-table tfoot { background: #1E293B; }
        .dpr-table tfoot td { font-weight: 800; font-size: 0.8125rem; }

        .dpr-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .dpr-empty svg { width: 2.5rem; height: 2.5rem; color: #CBD5E1; }
        .dpr-empty-title { font-weight: 700; color: var(--text); font-size: 0.875rem; margin: 0; }
        .dpr-empty-sub { font-size: 0.75rem; margin: 0; }

        .dpr-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dpr-page-list { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .dpr-page-btn {
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
        .dpr-page-btn:hover:not(:disabled) { background: #F9FAFB; }
        .dpr-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .dpr-page-btn.dpr-active { background: var(--gold); color: #fff; border-color: var(--gold); }
        .dark .dpr-page-btn { background: #1E293B; color: #CBD5E1; border-color: #334155; }

        @media print {
            body { background: #fff !important; }
            .dpr-no-print { display: none !important; }
            .fi-sidebar, .fi-topbar, aside, header, nav { display: none !important; }
            .dpr-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>

    <div class="dpr">

        {{-- ============================ FILTERS ============================ --}}
        <div class="dpr-card dpr-no-print report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memperbarui data..." />
            <div class="dpr-card-header">
                <div>
                    <h3 class="dpr-card-title">Filter Laporan</h3>
                    <p class="dpr-card-sub">Periode aktif: <strong style="color: var(--gold)">{{ $periodLabel }}</strong></p>
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
                    <button type="button" wire:click="resetFilters" class="dpr-btn dpr-btn-outline">
                        <svg class="dpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>

            <div class="dpr-filter-grid">
                <div>
                    <label class="dpr-label">Bulan</label>
                    <select wire:model.live="filter_month" class="dpr-select">
                        @foreach($monthOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="dpr-label">Tahun</label>
                    <select wire:model.live="filter_year" class="dpr-select">
                        @foreach($yearOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="dpr-label">Minggu</label>
                    <select wire:model.live="filter_week" class="dpr-select">
                        @foreach($weekOptions as $val => $label)
                            <option value="{{ $val === '' ? '' : $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="dpr-label">Nama Dokter</label>
                    <select wire:model.live="filter_dokter_user_id" class="dpr-select">
                        <option value="">Semua Dokter</option>
                        @foreach($doctorOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="dpr-label">Status Praktik</label>
                    <select wire:model.live="filter_status" class="dpr-select">
                        @foreach($statusOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="dpr-label">Cari</label>
                    <input type="search"
                           wire:model.live.debounce.500ms="filter_search"
                           placeholder="Dokter / pasien / treatment / kode"
                           class="dpr-input">
                </div>
            </div>
        </div>

        {{-- ============================ SUMMARY CARDS ============================ --}}
        <div class="dpr-summary-grid">
            <div class="dpr-stat">
                <div style="flex:1;">
                    <p class="dpr-stat-label">Dokter Aktif</p>
                    <p class="dpr-stat-value">{{ number_format($summary['total_active'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">orang</span></p>
                </div>
                <div class="dpr-stat-icon dpr-stat-icon-emerald">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>

            <div class="dpr-stat">
                <div style="flex:1;">
                    <p class="dpr-stat-label">Total Konsultasi</p>
                    <p class="dpr-stat-value">{{ number_format($summary['total_konsultasi'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">sesi</span></p>
                </div>
                <div class="dpr-stat-icon dpr-stat-icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>

            <div class="dpr-stat">
                <div style="flex:1;">
                    <p class="dpr-stat-label">Treatment Ditangani</p>
                    <p class="dpr-stat-value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">sesi</span></p>
                </div>
                <div class="dpr-stat-icon dpr-stat-icon-amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>

            <div class="dpr-stat dpr-stat-gold">
                <div style="flex:1;">
                    <p class="dpr-stat-label">Total Pendapatan</p>
                    <p class="dpr-stat-value">{{ $rupiah($summary['total_revenue']) }}</p>
                </div>
                <div class="dpr-stat-icon dpr-stat-icon-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>

            <div class="dpr-stat">
                <div style="flex:1;">
                    <p class="dpr-stat-label">Total Komisi</p>
                    <p class="dpr-stat-value dpr-stat-md" style="color:#047857;">{{ $rupiah($summary['total_commission']) }}</p>
                    <p class="dpr-stat-meta">15% konsul + 10% treatment</p>
                </div>
                <div class="dpr-stat-icon dpr-stat-icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ============================ EXPORT BAR ============================ --}}
        <div class="dpr-card dpr-no-print">
            <div class="dpr-export">
                <div>
                    <p style="margin:0;font-weight:700;color:var(--text);font-size:0.875rem;">Ekspor Laporan</p>
                    <p style="margin:0.25rem 0 0 0;font-size:0.75rem;color:var(--muted);">Mengikuti filter aktif. Total {{ number_format($totalRows, 0, ',', '.') }} dokter pada periode ini.</p>
                </div>
                <div class="dpr-export-buttons">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="dpr-btn dpr-btn-emerald">
                        <span wire:loading.remove wire:target="exportExcel" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="dpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Excel
                        </span>
                        <span wire:loading.flex wire:target="exportExcel" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="dpr-btn dpr-btn-blue">
                        <span wire:loading.remove wire:target="exportCsv" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="dpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            CSV
                        </span>
                        <span wire:loading.flex wire:target="exportCsv" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportPdf" wire:loading.attr="disabled" wire:target="exportPdf" class="dpr-btn dpr-btn-rose">
                        <span wire:loading.remove wire:target="exportPdf" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="dpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </span>
                        <span wire:loading.flex wire:target="exportPdf" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button onclick="window.print()" class="dpr-btn dpr-btn-charcoal">
                        <svg class="dpr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>
            <div wire:loading wire:target="exportExcel,exportCsv,exportPdf" class="dpr-loading-text">
                Memproses ekspor...
            </div>
        </div>

        {{-- ============================ CHARTS ============================ --}}
        <div class="dpr-chart-grid report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat grafik..." />
            <div class="dpr-card">
                <h3 class="dpr-card-title">Top 5 Dokter</h3>
                <p class="dpr-card-sub">Berdasarkan total layanan</p>
                <div class="dpr-chart-canvas">
                    <canvas id="dpr-chart-top"
                            data-labels='@json($chartTopDoctors['labels'])'
                            data-values='@json($chartTopDoctors['data'])'></canvas>
                </div>
            </div>

            <div class="dpr-card">
                <h3 class="dpr-card-title">Pendapatan per Dokter</h3>
                <p class="dpr-card-sub">Top 8 berdasarkan revenue</p>
                <div class="dpr-chart-canvas">
                    <canvas id="dpr-chart-revenue"
                            data-labels='@json($chartRevenue['labels'])'
                            data-values='@json($chartRevenue['data'])'></canvas>
                </div>
            </div>

            <div class="dpr-card">
                <h3 class="dpr-card-title">Komisi per Dokter</h3>
                <p class="dpr-card-sub">Top 8 berdasarkan komisi</p>
                <div class="dpr-chart-canvas">
                    <canvas id="dpr-chart-commission"
                            data-labels='@json($chartCommission['labels'])'
                            data-values='@json($chartCommission['data'])'></canvas>
                </div>
            </div>
        </div>

        {{-- ============================ WEEKLY RECAP ============================ --}}
        <div class="dpr-card report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat rekap mingguan..." />
            <div class="dpr-card-header">
                <div>
                    <h3 class="dpr-card-title">Rekap Mingguan</h3>
                    <p class="dpr-card-sub">Pembagian 4 minggu dalam bulan {{ $monthOptions[$this->filter_month] ?? '' }} {{ $this->filter_year }}</p>
                </div>
            </div>
            <div class="dpr-weekly-grid">
                @foreach($weeks as $w)
                    <div class="dpr-weekly-card">
                        <div class="dpr-weekly-head">
                            <span class="dpr-weekly-label">{{ $w['label'] }}</span>
                            <span class="dpr-weekly-range">{{ $w['range'] }}</span>
                        </div>
                        <div class="dpr-weekly-stats">
                            <div class="dpr-weekly-stat-row"><span class="label">Konsul Awal</span><span class="value">{{ number_format($w['konsultasi_awal'], 0, ',', '.') }}</span></div>
                            <div class="dpr-weekly-stat-row"><span class="label">Konsul Lanjutan</span><span class="value">{{ number_format($w['konsultasi_lanjutan'], 0, ',', '.') }}</span></div>
                            <div class="dpr-weekly-stat-row"><span class="label">Treatment</span><span class="value">{{ number_format($w['total_treatment'], 0, ',', '.') }}</span></div>
                        </div>
                        <p class="dpr-weekly-revenue">{{ $rupiah($w['total_revenue']) }}</p>
                        <span class="dpr-weekly-commission">Komisi {{ $rupiah($w['total_commission']) }}</span>
                        <div class="dpr-weekly-foot">
                            <p class="dpr-weekly-foot-label">Dokter Paling Aktif</p>
                            <p class="dpr-weekly-foot-name" title="{{ $w['best_doctor'] ?? '' }}">
                                {{ $w['best_doctor'] ?? '—' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="dpr-weekly-total">
                <span class="dpr-weekly-total-label">Total Bulanan</span>
                <span class="dpr-weekly-total-value">
                    {{ number_format($weeks->sum('konsultasi_awal') + $weeks->sum('konsultasi_lanjutan'), 0, ',', '.') }} konsul •
                    {{ number_format($weeks->sum('total_treatment'), 0, ',', '.') }} treatment •
                    {{ $rupiah($weeks->sum('total_revenue')) }} •
                    Komisi {{ $rupiah($weeks->sum('total_commission')) }}
                </span>
            </div>
        </div>

        {{-- ============================ TABLE ============================ --}}
        <div class="dpr-card dpr-card-flush report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat tabel laporan..." />
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;flex-wrap:wrap;">
                <div>
                    <h3 class="dpr-card-title">Detail Kinerja Dokter</h3>
                    <p class="dpr-card-sub">Total {{ number_format($totalRows, 0, ',', '.') }} dokter pada periode aktif</p>
                </div>
                <div class="dpr-no-print" style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-size:0.75rem;color:var(--muted);">Per halaman</label>
                    <select wire:model.live="per_page" class="dpr-select" style="width:auto;padding:0.25rem 2rem 0.25rem 0.625rem;font-size:0.75rem;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="dpr-table-wrap">
                <table class="dpr-table">
                    <thead>
                        <tr>
                            <th style="width:3rem;">No</th>
                            <th class="dpr-sortable" wire:click="sortBy('dokter_name')">
                                <span class="dpr-th-content">Nama Dokter @include('filament.pages.partials.sort-icon-doctor', ['field' => 'dokter_name'])</span>
                            </th>
                            <th class="dpr-text-center dpr-sortable" wire:click="sortBy('konsultasi_awal')">
                                <span class="dpr-th-content">Konsul Awal @include('filament.pages.partials.sort-icon-doctor', ['field' => 'konsultasi_awal'])</span>
                            </th>
                            <th class="dpr-text-center dpr-sortable" wire:click="sortBy('konsultasi_lanjutan')">
                                <span class="dpr-th-content">Konsul Lanjutan @include('filament.pages.partials.sort-icon-doctor', ['field' => 'konsultasi_lanjutan'])</span>
                            </th>
                            <th class="dpr-text-center dpr-sortable" wire:click="sortBy('total_konsultasi')">
                                <span class="dpr-th-content">Total Konsul @include('filament.pages.partials.sort-icon-doctor', ['field' => 'total_konsultasi'])</span>
                            </th>
                            <th>Treatment Ditangani</th>
                            <th class="dpr-text-center dpr-sortable" wire:click="sortBy('total_treatment')">
                                <span class="dpr-th-content">Total Treatment @include('filament.pages.partials.sort-icon-doctor', ['field' => 'total_treatment'])</span>
                            </th>
                            <th class="dpr-text-center dpr-sortable" wire:click="sortBy('total_pasien')">
                                <span class="dpr-th-content">Total Pasien @include('filament.pages.partials.sort-icon-doctor', ['field' => 'total_pasien'])</span>
                            </th>
                            <th class="dpr-text-right dpr-sortable" wire:click="sortBy('total_revenue')">
                                <span class="dpr-th-content">Total Pendapatan @include('filament.pages.partials.sort-icon-doctor', ['field' => 'total_revenue'])</span>
                            </th>
                            <th class="dpr-text-right">Komisi Konsul (15%)</th>
                            <th class="dpr-text-right">Komisi Treatment (10%)</th>
                            <th class="dpr-text-right dpr-sortable" wire:click="sortBy('total_komisi')">
                                <span class="dpr-th-content">Total Komisi @include('filament.pages.partials.sort-icon-doctor', ['field' => 'total_komisi'])</span>
                            </th>
                            <th>Minggu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $r)
                            <tr>
                                <td class="dpr-no">{{ ($this->page - 1) * $this->per_page + $i + 1 }}</td>
                                <td>
                                    <p class="dpr-name">{{ $r['dokter_name'] }}</p>
                                    <span class="dpr-name-sub">{{ $r['kode_dokter'] }}</span>
                                </td>
                                <td class="dpr-text-center dpr-num-konsultasi">{{ number_format($r['konsultasi_awal'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center dpr-num-konsultasi">{{ number_format($r['konsultasi_lanjutan'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center dpr-num">{{ number_format($r['total_konsultasi'], 0, ',', '.') }}</td>
                                <td>
                                    @if(!empty($r['treatment_list']))
                                        <div class="dpr-treatment-list">
                                            @foreach(array_slice($r['treatment_list'], 0, 3) as $t)
                                                <span class="dpr-treatment-chip" title="{{ $t['name'] }}">
                                                    {{ \Illuminate\Support\Str::limit($t['name'], 20) }} {{ $t['qty'] }}x
                                                </span>
                                            @endforeach
                                            @if(count($r['treatment_list']) > 3)
                                                <span class="dpr-treatment-chip dpr-treatment-chip-more">
                                                    +{{ count($r['treatment_list']) - 3 }} lainnya
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="dpr-meta-cell">—</span>
                                    @endif
                                </td>
                                <td class="dpr-text-center dpr-num-treatment">{{ number_format($r['total_treatment'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center dpr-num">{{ number_format($r['total_pasien'], 0, ',', '.') }}</td>
                                <td class="dpr-text-right dpr-revenue">{{ $rupiah($r['total_revenue']) }}</td>
                                <td class="dpr-text-right dpr-commission-secondary">{{ $rupiah($r['komisi_konsultasi']) }}</td>
                                <td class="dpr-text-right dpr-commission-secondary">{{ $rupiah($r['komisi_treatment']) }}</td>
                                <td class="dpr-text-right dpr-commission">{{ $rupiah($r['total_komisi']) }}</td>
                                <td class="dpr-meta-cell">{{ $r['weeks_label'] }}</td>
                                <td>
                                    @if($r['status_praktik'] === 'aktif')
                                        <span class="dpr-status-aktif">Aktif</span>
                                    @else
                                        <span class="dpr-status-nonaktif">{{ ucfirst($r['status_praktik']) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14">
                                    <div class="dpr-empty">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <p class="dpr-empty-title">Belum ada data kinerja dokter pada periode ini.</p>
                                        <p class="dpr-empty-sub">Hanya konsultasi & treatment dengan pembayaran sudah dibayar dan appointment selesai yang dihitung.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalRows > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2" class="dpr-text-right" style="color:#475569;">TOTAL</td>
                                <td class="dpr-text-center">{{ number_format($tableTotals['konsultasi_awal'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center">{{ number_format($tableTotals['konsultasi_lanjutan'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center">{{ number_format($tableTotals['total_konsultasi'], 0, ',', '.') }}</td>
                                <td>—</td>
                                <td class="dpr-text-center">{{ number_format($tableTotals['total_treatment'], 0, ',', '.') }}</td>
                                <td class="dpr-text-center">{{ number_format($tableTotals['total_pasien'], 0, ',', '.') }}</td>
                                <td class="dpr-text-right" style="color:var(--gold);">{{ $rupiah($tableTotals['total_revenue']) }}</td>
                                <td class="dpr-text-right" style="color:#047857;">{{ $rupiah($tableTotals['komisi_konsultasi']) }}</td>
                                <td class="dpr-text-right" style="color:#047857;">{{ $rupiah($tableTotals['komisi_treatment']) }}</td>
                                <td class="dpr-text-right" style="color:#047857;">{{ $rupiah($tableTotals['total_komisi']) }}</td>
                                <td colspan="2" style="font-size:0.75rem;color:var(--muted);font-style:italic;font-weight:400;">{{ $tableTotals['doctor_count'] }} dokter</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($totalPages > 1)
                <div class="dpr-pagination dpr-no-print">
                    <p style="color:var(--muted);margin:0;">
                        Halaman <strong style="color:var(--text);">{{ $this->page }}</strong> dari <strong style="color:var(--text);">{{ $totalPages }}</strong>
                    </p>
                    <div class="dpr-page-list">
                        <button wire:click="previousPage" @disabled($this->page <= 1) class="dpr-page-btn">← Sebelumnya</button>
                        @for($p = max(1, $this->page - 2); $p <= min($totalPages, $this->page + 2); $p++)
                            <button wire:click="gotoPage({{ $p }})" class="dpr-page-btn {{ $p === $this->page ? 'dpr-active' : '' }}">{{ $p }}</button>
                        @endfor
                        <button wire:click="nextPage" @disabled($this->page >= $totalPages) class="dpr-page-btn">Selanjutnya →</button>
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

                function renderDprCharts() {
                    if (typeof Chart === 'undefined') return;

                    document.querySelectorAll('.dpr canvas').forEach(c => {
                        const existing = Chart.getChart(c);
                        if (existing) existing.destroy();
                    });

                    const cv1 = document.getElementById('dpr-chart-top');
                    if (cv1) {
                        const labels = JSON.parse(cv1.dataset.labels || '[]');
                        const values = JSON.parse(cv1.dataset.values || '[]');
                        new Chart(cv1, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Total Layanan',
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

                    const cv2 = document.getElementById('dpr-chart-revenue');
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

                    const cv3 = document.getElementById('dpr-chart-commission');
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

                document.addEventListener('DOMContentLoaded', renderDprCharts);
                document.addEventListener('livewire:navigated', renderDprCharts);
                if (window.Livewire) {
                    window.Livewire.hook('morph.updated', renderDprCharts);
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
