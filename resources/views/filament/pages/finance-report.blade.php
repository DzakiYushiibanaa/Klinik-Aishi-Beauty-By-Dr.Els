@php
    $data = $this->viewData;
    $summary = $data['summary'];
    $rows = $data['rows'];
    $totalRows = $data['totalRows'];
    $totalPages = $data['totalPages'];
    $tableTotals = $data['tableTotals'];
    $weeks = $data['weeks'];
    $chartRevenueWeek = $data['chartRevenueWeek'];
    $chartComposition = $data['chartComposition'];
    $chartPaymentMethod = $data['chartPaymentMethod'];
    $periodLabel = $data['periodLabel'];

    $monthOptions = $data['monthOptions'];
    $yearOptions = $data['yearOptions'];
    $weekOptions = $data['weekOptions'];
    $orderTypeOptions = $data['orderTypeOptions'];
    $paymentMethodOptions = $data['paymentMethodOptions'];
    $paymentStatusOptions = $data['paymentStatusOptions'];

    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $loadingTargets = 'filter_month,filter_year,filter_week,filter_order_type,filter_payment_method,filter_payment_status,filter_search,sortBy,gotoPage,nextPage,previousPage,per_page,resetFilters';
@endphp

<x-filament-panels::page>

@include('filament.partials.report-loading-styles')

    @push('scripts')
        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endonce
    @endpush

    <style>
        .fnr {
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
        .dark .fnr { --text: #F9FAFB; --muted: #94A3B8; --border: #334155; }

        .fnr * { box-sizing: border-box; }

        .fnr-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.25rem;
        }
        .dark .fnr-card { background: #0F172A; }
        .fnr-card-flush { padding: 0; overflow: hidden; }

        .fnr-card h3.fnr-card-title { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text); }
        .fnr-card .fnr-card-sub { font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem; }
        .fnr-card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }

        /* FILTER */
        .fnr-filter-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .fnr-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .fnr-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .fnr-filter-grid .fnr-col-2 { grid-column: span 1 / span 1; }
        @media (min-width: 640px) { .fnr-filter-grid .fnr-col-2 { grid-column: span 2 / span 2; } }

        .fnr-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }
        .dark .fnr-label { color: #CBD5E1; }

        .fnr-input,
        .fnr-select {
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
        .dark .fnr-input, .dark .fnr-select { background: #1E293B; border-color: #475569; color: #F9FAFB; }
        .fnr-input:focus, .fnr-select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184,149,111,0.25);
        }
        .fnr-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.625rem center;
            background-size: 1.125rem;
            padding-right: 2rem;
        }

        /* BUTTON */
        .fnr-btn {
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
        .fnr-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .fnr-btn-outline { background: #fff; color: #475569; border: 1px solid var(--border); }
        .fnr-btn-outline:hover:not(:disabled) { background: #F9FAFB; }
        .dark .fnr-btn-outline { background: #1E293B; color: #CBD5E1; }
        .fnr-btn-emerald { background: #059669; color: #fff; }
        .fnr-btn-emerald:hover:not(:disabled) { background: #047857; }
        .fnr-btn-blue { background: #2563EB; color: #fff; }
        .fnr-btn-blue:hover:not(:disabled) { background: #1D4ED8; }
        .fnr-btn-rose { background: #E11D48; color: #fff; }
        .fnr-btn-rose:hover:not(:disabled) { background: #BE123C; }
        .fnr-btn-charcoal { background: var(--charcoal); color: #fff; }
        .fnr-btn-charcoal:hover:not(:disabled) { background: #1A1A1A; }
        .fnr-btn-icon { width: 0.875rem; height: 0.875rem; stroke-width: 2; flex-shrink: 0; }

        /* SUMMARY */
        .fnr-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) { .fnr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .fnr-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .fnr-summary-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); } }

        .fnr-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            transition: transform 200ms, box-shadow 200ms;
        }
        .fnr-stat:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
        .dark .fnr-stat { background: #0F172A; }

        .fnr-stat-gold {
            background: linear-gradient(135deg, #B8956F, #A07E5A);
            color: #fff;
            border: 0;
            box-shadow: 0 4px 12px rgba(184,149,111,0.25);
        }
        .fnr-stat-gold .fnr-stat-label, .fnr-stat-gold .fnr-stat-value { color: #fff; }

        .fnr-stat-label {
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748B;
            margin: 0;
        }
        .fnr-stat-gold .fnr-stat-label { color: rgba(255,255,255,0.85); }

        .fnr-stat-value {
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.375rem 0 0 0;
            line-height: 1.2;
            word-break: break-word;
        }
        .fnr-stat-meta { font-size: 0.6875rem; color: var(--muted); margin: 0.25rem 0 0 0; }

        .fnr-stat-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fnr-stat-icon svg { width: 1rem; height: 1rem; }
        .fnr-stat-icon-emerald { background: #D1FAE5; color: #047857; }
        .fnr-stat-icon-amber { background: #FEF3C7; color: #B45309; }
        .fnr-stat-icon-blue { background: #DBEAFE; color: #1D4ED8; }
        .fnr-stat-icon-purple { background: #EDE9FE; color: #6D28D9; }
        .fnr-stat-icon-rose { background: #FFE4E6; color: #BE123C; }
        .fnr-stat-icon-white { background: rgba(255,255,255,0.2); color: #fff; }

        /* EXPORT */
        .fnr-export { display: flex; flex-direction: column; gap: 0.75rem; }
        @media (min-width: 640px) {
            .fnr-export { flex-direction: row; align-items: center; justify-content: space-between; }
        }
        .fnr-export-buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .fnr-loading-text { margin-top: 0.75rem; font-size: 0.75rem; color: var(--muted); font-style: italic; }

        /* CHART */
        .fnr-chart-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .fnr-chart-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fnr-chart-canvas { position: relative; height: 240px; margin-top: 0.5rem; }

        /* WEEKLY */
        .fnr-weekly-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .fnr-weekly-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .fnr-weekly-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .fnr-weekly-card {
            background: var(--gold-soft);
            border: 1px solid var(--stone);
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .dark .fnr-weekly-card { background: #1E293B; border-color: #334155; }

        .fnr-weekly-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .fnr-weekly-label { font-size: 0.75rem; font-weight: 700; color: var(--gold); letter-spacing: 0.03em; }
        .fnr-weekly-range { font-size: 0.625rem; color: var(--muted); }
        .fnr-weekly-revenue { font-size: 1.125rem; font-weight: 800; color: var(--text); margin: 0.5rem 0 0 0; }

        .fnr-weekly-stats {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border);
        }
        .fnr-weekly-stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.6875rem;
        }
        .fnr-weekly-stat-row .label { color: var(--muted); }
        .fnr-weekly-stat-row .value { font-weight: 700; color: var(--text); }

        .fnr-weekly-trans {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.125rem 0.5rem;
            background: #DBEAFE;
            color: #1D4ED8;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .dark .fnr-weekly-trans { background: #1E3A8A; color: #BFDBFE; }

        .fnr-weekly-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.875rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .fnr-weekly-total-label { font-weight: 700; color: var(--text); }
        .fnr-weekly-total-value { font-weight: 800; color: var(--gold); }

        /* TABLE */
        .fnr-table-wrap { overflow-x: auto; }
        .fnr-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        .fnr-table thead {
            background: var(--charcoal);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .fnr-table thead th {
            text-align: left;
            padding: 0.75rem 0.875rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }
        .fnr-table thead th.fnr-sortable { cursor: pointer; user-select: none; }
        .fnr-table thead th.fnr-sortable:hover { background: #1A1A1A; }
        .fnr-th-content { display: inline-flex; align-items: center; gap: 0.25rem; }
        .fnr-table thead th.fnr-text-right .fnr-th-content { float: right; }
        .fnr-table thead th.fnr-text-center .fnr-th-content { display: flex; justify-content: center; }

        .fnr-table tbody tr { border-top: 1px solid #F3F4F6; transition: background 150ms; }
        .dark .fnr-table tbody tr { border-top-color: #1E293B; }
        .fnr-table tbody tr:hover { background: var(--gold-soft); }
        .dark .fnr-table tbody tr:hover { background: #1E293B; }

        .fnr-table td { padding: 0.75rem 0.875rem; color: #334155; vertical-align: middle; }
        .dark .fnr-table td { color: #E2E8F0; }
        .fnr-text-right { text-align: right; }
        .fnr-text-center { text-align: center; }

        .fnr-no { color: var(--muted); font-size: 0.75rem; }
        .fnr-name { font-weight: 700; color: var(--text); margin: 0; }
        .fnr-kode {
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gold);
        }
        .fnr-meta-cell { font-size: 0.75rem; color: var(--muted); }

        .fnr-type-chip {
            display: inline-block;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .fnr-type-chip-produk { background: #DBEAFE; color: #1D4ED8; }
        .fnr-type-chip-treatment { background: #F2EFE9; color: #8C7A63; }
        .fnr-type-chip-konsultasi { background: #FEF3C7; color: #B45309; }
        .fnr-type-chip-membership { background: #EDE9FE; color: #6D28D9; }
        .fnr-type-chip-default { background: #F3F4F6; color: #6B7280; }

        .fnr-method-chip {
            display: inline-block;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            background: #F3F4F6;
            color: #475569;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .dark .fnr-method-chip { background: #1E293B; color: #CBD5E1; }

        .fnr-status-paid {
            display: inline-block;
            background: #D1FAE5;
            color: #047857;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }

        .fnr-num { font-weight: 600; color: #334155; }
        .fnr-grand { font-weight: 800; color: var(--gold); }

        .fnr-table tfoot { background: var(--gold-soft); border-top: 2px solid rgba(184,149,111,0.4); }
        .dark .fnr-table tfoot { background: #1E293B; }
        .fnr-table tfoot td { font-weight: 800; font-size: 0.8125rem; }

        .fnr-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .fnr-empty svg { width: 2.5rem; height: 2.5rem; color: #CBD5E1; }
        .fnr-empty-title { font-weight: 700; color: var(--text); font-size: 0.875rem; margin: 0; }
        .fnr-empty-sub { font-size: 0.75rem; margin: 0; }

        .fnr-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .fnr-page-list { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .fnr-page-btn {
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
        .fnr-page-btn:hover:not(:disabled) { background: #F9FAFB; }
        .fnr-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .fnr-page-btn.fnr-active { background: var(--gold); color: #fff; border-color: var(--gold); }
        .dark .fnr-page-btn { background: #1E293B; color: #CBD5E1; border-color: #334155; }

        @media print {
            body { background: #fff !important; }
            .fnr-no-print { display: none !important; }
            .fi-sidebar, .fi-topbar, aside, header, nav { display: none !important; }
            .fnr-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>

    <div class="fnr">

        {{-- ============================ FILTERS ============================ --}}
        <div class="fnr-card fnr-no-print report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memperbarui data..." />
            <div class="fnr-card-header">
                <div>
                    <h3 class="fnr-card-title">Filter Laporan</h3>
                    <p class="fnr-card-sub">Periode aktif: <strong style="color: var(--gold)">{{ $periodLabel }}</strong></p>
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
                    <button type="button" wire:click="resetFilters" class="fnr-btn fnr-btn-outline">
                        <svg class="fnr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>

            <div class="fnr-filter-grid">
                <div>
                    <label class="fnr-label">Bulan</label>
                    <select wire:model.live="filter_month" class="fnr-select">
                        @foreach($monthOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fnr-label">Tahun</label>
                    <select wire:model.live="filter_year" class="fnr-select">
                        @foreach($yearOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fnr-label">Minggu</label>
                    <select wire:model.live="filter_week" class="fnr-select">
                        @foreach($weekOptions as $val => $label)
                            <option value="{{ $val === '' ? '' : $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fnr-label">Jenis Transaksi</label>
                    <select wire:model.live="filter_order_type" class="fnr-select">
                        @foreach($orderTypeOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fnr-label">Metode Pembayaran</label>
                    <select wire:model.live="filter_payment_method" class="fnr-select">
                        @foreach($paymentMethodOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fnr-label">Status Pembayaran</label>
                    <select wire:model.live="filter_payment_status" class="fnr-select">
                        @foreach($paymentStatusOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fnr-col-2">
                    <label class="fnr-label">Cari</label>
                    <input type="search"
                           wire:model.live.debounce.500ms="filter_search"
                           placeholder="Invoice / nama customer / produk / treatment"
                           class="fnr-input">
                </div>
            </div>
        </div>

        {{-- ============================ SUMMARY CARDS ============================ --}}
        <div class="fnr-summary-grid">
            <div class="fnr-stat fnr-stat-gold">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Total Pendapatan</p>
                    <p class="fnr-stat-value">{{ $rupiah($summary['total_revenue']) }}</p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>

            <div class="fnr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Pendapatan Produk</p>
                    <p class="fnr-stat-value">{{ $rupiah($summary['revenue_produk']) }}</p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>

            <div class="fnr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Pendapatan Treatment</p>
                    <p class="fnr-stat-value">{{ $rupiah($summary['revenue_treatment']) }}</p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>

            <div class="fnr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Pendapatan Konsultasi</p>
                    <p class="fnr-stat-value">{{ $rupiah($summary['revenue_konsultasi']) }}</p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>

            <div class="fnr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Total Ongkir</p>
                    <p class="fnr-stat-value">{{ $rupiah($summary['total_ongkir']) }}</p>
                    <p class="fnr-stat-meta">Hanya pengiriman produk</p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-rose">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1m0 0V6a1 1 0 011-1h2a1 1 0 011 1v4l4 2v6h-3"/>
                    </svg>
                </div>
            </div>

            <div class="fnr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="fnr-stat-label">Total Transaksi Sudah Dibayar</p>
                    <p class="fnr-stat-value">{{ number_format($summary['total_transactions'], 0, ',', '.') }} <span style="font-size:0.75rem;color:#94A3B8;font-weight:500;">tx</span></p>
                </div>
                <div class="fnr-stat-icon fnr-stat-icon-emerald">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ============================ EXPORT BAR ============================ --}}
        <div class="fnr-card fnr-no-print">
            <div class="fnr-export">
                <div>
                    <p style="margin:0;font-weight:700;color:var(--text);font-size:0.875rem;">Ekspor Laporan</p>
                    <p style="margin:0.25rem 0 0 0;font-size:0.75rem;color:var(--muted);">Mengikuti filter aktif. Total {{ number_format($totalRows, 0, ',', '.') }} transaksi pada periode ini.</p>
                </div>
                <div class="fnr-export-buttons">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="fnr-btn fnr-btn-emerald">
                        <span wire:loading.remove wire:target="exportExcel" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="fnr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Excel
                        </span>
                        <span wire:loading.flex wire:target="exportExcel" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="fnr-btn fnr-btn-blue">
                        <span wire:loading.remove wire:target="exportCsv" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="fnr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            CSV
                        </span>
                        <span wire:loading.flex wire:target="exportCsv" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportPdf" wire:loading.attr="disabled" wire:target="exportPdf" class="fnr-btn fnr-btn-rose">
                        <span wire:loading.remove wire:target="exportPdf" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="fnr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </span>
                        <span wire:loading.flex wire:target="exportPdf" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button onclick="window.print()" class="fnr-btn fnr-btn-charcoal">
                        <svg class="fnr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>
            <div wire:loading wire:target="exportExcel,exportCsv,exportPdf" class="fnr-loading-text">
                Memproses ekspor...
            </div>
        </div>

        {{-- ============================ CHARTS ============================ --}}
        <div class="fnr-chart-grid report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat grafik..." />
            <div class="fnr-card">
                <h3 class="fnr-card-title">Pendapatan per Minggu</h3>
                <p class="fnr-card-sub">Trend revenue klinik per minggu</p>
                <div class="fnr-chart-canvas">
                    <canvas id="fnr-chart-revenue"
                            data-labels='@json($chartRevenueWeek['labels'])'
                            data-values='@json($chartRevenueWeek['data'])'></canvas>
                </div>
            </div>

            <div class="fnr-card">
                <h3 class="fnr-card-title">Komposisi Pendapatan</h3>
                <p class="fnr-card-sub">Breakdown produk / treatment / konsultasi</p>
                <div class="fnr-chart-canvas">
                    <canvas id="fnr-chart-composition"
                            data-labels='@json($chartComposition['labels'])'
                            data-values='@json($chartComposition['data'])'></canvas>
                </div>
            </div>

            <div class="fnr-card">
                <h3 class="fnr-card-title">Metode Pembayaran</h3>
                <p class="fnr-card-sub">Distribusi total pembayaran per metode</p>
                <div class="fnr-chart-canvas">
                    <canvas id="fnr-chart-method"
                            data-labels='@json($chartPaymentMethod['labels'])'
                            data-values='@json($chartPaymentMethod['data'])'></canvas>
                </div>
            </div>
        </div>

        {{-- ============================ WEEKLY RECAP ============================ --}}
        <div class="fnr-card report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat rekap mingguan..." />
            <div class="fnr-card-header">
                <div>
                    <h3 class="fnr-card-title">Rekap Mingguan</h3>
                    <p class="fnr-card-sub">Pembagian 4 minggu dalam bulan {{ $monthOptions[$this->filter_month] ?? '' }} {{ $this->filter_year }}</p>
                </div>
            </div>
            <div class="fnr-weekly-grid">
                @foreach($weeks as $w)
                    <div class="fnr-weekly-card">
                        <div class="fnr-weekly-head">
                            <span class="fnr-weekly-label">{{ $w['label'] }}</span>
                            <span class="fnr-weekly-range">{{ $w['range'] }}</span>
                        </div>
                        <p class="fnr-weekly-revenue">{{ $rupiah($w['total_revenue']) }}</p>
                        <span class="fnr-weekly-trans">{{ number_format($w['total_transactions'], 0, ',', '.') }} transaksi</span>
                        <div class="fnr-weekly-stats">
                            <div class="fnr-weekly-stat-row"><span class="label">Produk</span><span class="value">{{ $rupiah($w['revenue_produk']) }}</span></div>
                            <div class="fnr-weekly-stat-row"><span class="label">Treatment</span><span class="value">{{ $rupiah($w['revenue_treatment']) }}</span></div>
                            <div class="fnr-weekly-stat-row"><span class="label">Konsultasi</span><span class="value">{{ $rupiah($w['revenue_konsultasi']) }}</span></div>
                            <div class="fnr-weekly-stat-row"><span class="label">Ongkir</span><span class="value">{{ $rupiah($w['total_ongkir']) }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="fnr-weekly-total">
                <span class="fnr-weekly-total-label">Total Pendapatan Bulanan</span>
                <span class="fnr-weekly-total-value">
                    {{ $rupiah($weeks->sum('total_revenue')) }} •
                    {{ number_format($weeks->sum('total_transactions'), 0, ',', '.') }} transaksi •
                    Ongkir {{ $rupiah($weeks->sum('total_ongkir')) }}
                </span>
            </div>
        </div>

        {{-- ============================ TABLE ============================ --}}
        <div class="fnr-card fnr-card-flush report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat tabel laporan..." />
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;flex-wrap:wrap;">
                <div>
                    <h3 class="fnr-card-title">Detail Transaksi Keuangan</h3>
                    <p class="fnr-card-sub">Total {{ number_format($totalRows, 0, ',', '.') }} transaksi pada periode aktif</p>
                </div>
                <div class="fnr-no-print" style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-size:0.75rem;color:var(--muted);">Per halaman</label>
                    <select wire:model.live="per_page" class="fnr-select" style="width:auto;padding:0.25rem 2rem 0.25rem 0.625rem;font-size:0.75rem;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="fnr-table-wrap">
                <table class="fnr-table">
                    <thead>
                        <tr>
                            <th style="width:3rem;">No</th>
                            <th class="fnr-sortable" wire:click="sortBy('kode_order')">
                                <span class="fnr-th-content">Invoice @include('filament.pages.partials.sort-icon-finance', ['field' => 'kode_order'])</span>
                            </th>
                            <th class="fnr-sortable" wire:click="sortBy('paid_at')">
                                <span class="fnr-th-content">Tanggal Bayar @include('filament.pages.partials.sort-icon-finance', ['field' => 'paid_at'])</span>
                            </th>
                            <th class="fnr-sortable" wire:click="sortBy('pasien')">
                                <span class="fnr-th-content">Customer @include('filament.pages.partials.sort-icon-finance', ['field' => 'pasien'])</span>
                            </th>
                            <th>Jenis</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th class="fnr-text-right fnr-sortable" wire:click="sortBy('subtotal')">
                                <span class="fnr-th-content">Subtotal @include('filament.pages.partials.sort-icon-finance', ['field' => 'subtotal'])</span>
                            </th>
                            <th class="fnr-text-right fnr-sortable" wire:click="sortBy('ongkir')">
                                <span class="fnr-th-content">Ongkir @include('filament.pages.partials.sort-icon-finance', ['field' => 'ongkir'])</span>
                            </th>
                            <th class="fnr-text-right fnr-sortable" wire:click="sortBy('diskon')">
                                <span class="fnr-th-content">Diskon @include('filament.pages.partials.sort-icon-finance', ['field' => 'diskon'])</span>
                            </th>
                            <th class="fnr-text-right fnr-sortable" wire:click="sortBy('grand_total')">
                                <span class="fnr-th-content">Total Bayar @include('filament.pages.partials.sort-icon-finance', ['field' => 'grand_total'])</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $r)
                            <tr>
                                <td class="fnr-no">{{ ($this->page - 1) * $this->per_page + $i + 1 }}</td>
                                <td><span class="fnr-kode">{{ $r['kode_order'] }}</span></td>
                                <td class="fnr-meta-cell">{{ $r['paid_at_label'] }}</td>
                                <td class="fnr-name">{{ $r['pasien'] }}</td>
                                <td>
                                    <span class="fnr-type-chip
                                        @switch($r['order_type'])
                                            @case('produk') fnr-type-chip-produk @break
                                            @case('treatment') fnr-type-chip-treatment @break
                                            @case('konsultasi') fnr-type-chip-konsultasi @break
                                            @case('membership') fnr-type-chip-membership @break
                                            @default fnr-type-chip-default
                                        @endswitch">
                                        {{ $r['order_type_label'] }}
                                    </span>
                                </td>
                                <td><span class="fnr-method-chip">{{ $r['payment_method_label'] }}</span></td>
                                <td><span class="fnr-status-paid">{{ $r['status_label'] }}</span></td>
                                <td class="fnr-text-right fnr-num">{{ $rupiah($r['subtotal']) }}</td>
                                <td class="fnr-text-right fnr-num">{{ $rupiah($r['ongkir']) }}</td>
                                <td class="fnr-text-right fnr-num">{{ $r['diskon'] > 0 ? '−' . $rupiah($r['diskon']) : $rupiah(0) }}</td>
                                <td class="fnr-text-right fnr-grand">{{ $rupiah($r['grand_total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="fnr-empty">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                                        </svg>
                                        <p class="fnr-empty-title">Belum ada data keuangan pada periode ini.</p>
                                        <p class="fnr-empty-sub">Coba ubah filter periode atau metode pembayaran.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalRows > 0)
                        <tfoot>
                            <tr>
                                <td colspan="7" class="fnr-text-right" style="color:#475569;">TOTAL</td>
                                <td class="fnr-text-right">{{ $rupiah($tableTotals['subtotal']) }}</td>
                                <td class="fnr-text-right">{{ $rupiah($tableTotals['ongkir']) }}</td>
                                <td class="fnr-text-right">{{ $tableTotals['diskon'] > 0 ? '−' . $rupiah($tableTotals['diskon']) : $rupiah(0) }}</td>
                                <td class="fnr-text-right" style="color:var(--gold);">{{ $rupiah($tableTotals['grand_total']) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($totalPages > 1)
                <div class="fnr-pagination fnr-no-print">
                    <p style="color:var(--muted);margin:0;">
                        Halaman <strong style="color:var(--text);">{{ $this->page }}</strong> dari <strong style="color:var(--text);">{{ $totalPages }}</strong>
                    </p>
                    <div class="fnr-page-list">
                        <button wire:click="previousPage" @disabled($this->page <= 1) class="fnr-page-btn">← Sebelumnya</button>
                        @for($p = max(1, $this->page - 2); $p <= min($totalPages, $this->page + 2); $p++)
                            <button wire:click="gotoPage({{ $p }})" class="fnr-page-btn {{ $p === $this->page ? 'fnr-active' : '' }}">{{ $p }}</button>
                        @endfor
                        <button wire:click="nextPage" @disabled($this->page >= $totalPages) class="fnr-page-btn">Selanjutnya →</button>
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

                const palette = ['#B8956F', '#3B3529', '#8C7A63', '#7C3AED', '#059669', '#2563EB', '#E11D48', '#D97706'];

                function renderFnrCharts() {
                    if (typeof Chart === 'undefined') return;

                    document.querySelectorAll('.fnr canvas').forEach(c => {
                        const existing = Chart.getChart(c);
                        if (existing) existing.destroy();
                    });

                    const goldGradient = (ctx) => {
                        const g = ctx.createLinearGradient(0, 0, 0, 240);
                        g.addColorStop(0, 'rgba(184,149,111,0.8)');
                        g.addColorStop(1, 'rgba(184,149,111,0.1)');
                        return g;
                    };

                    // Chart 1: Revenue per minggu (line)
                    const cv1 = document.getElementById('fnr-chart-revenue');
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
                                    pointRadius: 5,
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

                    // Chart 2: Komposisi (doughnut)
                    const cv2 = document.getElementById('fnr-chart-composition');
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
                                    legend: { position: 'bottom', labels: { font: { size: 11 } } },
                                    tooltip: {
                                        enabled: hasData,
                                        callbacks: { label: (ctx) => `${ctx.label}: ${rupiah(ctx.parsed)}` },
                                    },
                                },
                            },
                        });
                    }

                    // Chart 3: Metode pembayaran (bar)
                    const cv3 = document.getElementById('fnr-chart-method');
                    if (cv3) {
                        const labels = JSON.parse(cv3.dataset.labels || '[]');
                        const values = JSON.parse(cv3.dataset.values || '[]');
                        new Chart(cv3, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Total Pembayaran',
                                    data: values,
                                    backgroundColor: '#3B3529',
                                    borderRadius: 8,
                                }],
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: (ctx) => rupiah(ctx.parsed.x) } },
                                },
                                scales: {
                                    x: { ticks: { callback: (v) => rupiah(v) } },
                                },
                            },
                        });
                    }
                }

                document.addEventListener('DOMContentLoaded', renderFnrCharts);
                document.addEventListener('livewire:navigated', renderFnrCharts);
                if (window.Livewire) {
                    window.Livewire.hook('morph.updated', renderFnrCharts);
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
