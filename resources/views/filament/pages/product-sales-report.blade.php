@php
    $data = $this->viewData;
    $summary = $data['summary'];
    $rows = $data['rows'];
    $totalRows = $data['totalRows'];
    $totalPages = $data['totalPages'];
    $tableTotals = $data['tableTotals'];
    $weeks = $data['weeks'];
    $chartRevenue = $data['chartRevenue'];
    $chartTopProducts = $data['chartTopProducts'];
    $chartQty = $data['chartQty'];
    $periodLabel = $data['periodLabel'];

    $monthOptions = $data['monthOptions'];
    $yearOptions = $data['yearOptions'];
    $weekOptions = $data['weekOptions'];
    $productsOptions = $data['productsOptions'];
    $categoriesOptions = $data['categoriesOptions'];
    $paymentStatusOptions = $data['paymentStatusOptions'];

    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $loadingTargets = 'filter_month,filter_year,filter_week,filter_payment_status,filter_category_id,filter_product_id,filter_search,sortBy,gotoPage,nextPage,previousPage,per_page,resetFilters';
@endphp

<x-filament-panels::page>

@include('filament.partials.report-loading-styles')

    {{-- Inject Chart.js dari CDN (single load) --}}
    @push('scripts')
        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endonce
    @endpush

    {{-- ================ SCOPED STYLES ================ --}}
    <style>
        .psr {
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
            --emerald: #059669;
            --emerald-soft: #D1FAE5;
            --rose: #E11D48;
            --rose-soft: #FFE4E6;
            --blue: #2563EB;
            --blue-soft: #DBEAFE;
            --amber: #D97706;
            --amber-soft: #FEF3C7;
            --purple: #7C3AED;
            --purple-soft: #EDE9FE;

            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .dark .psr { --text: #F9FAFB; --muted: #94A3B8; --border: #334155; }

        .psr * { box-sizing: border-box; }

        /* Card base */
        .psr-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.25rem;
        }
        .dark .psr-card { background: #0F172A; }

        .psr-card-flush { padding: 0; overflow: hidden; }

        .psr-card h3.psr-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }
        .psr-card .psr-card-sub {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }
        .psr-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        /* ================ FILTER ================ */
        .psr-filter-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .psr-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .psr-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .psr-filter-grid .psr-col-2 { grid-column: span 1 / span 1; }
        @media (min-width: 640px) { .psr-filter-grid .psr-col-2 { grid-column: span 2 / span 2; } }
        @media (min-width: 1024px) { .psr-filter-grid .psr-col-2 { grid-column: span 2 / span 2; } }

        .psr-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }
        .dark .psr-label { color: #CBD5E1; }

        .psr-input,
        .psr-select {
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
        .dark .psr-input, .dark .psr-select { background: #1E293B; border-color: #475569; color: #F9FAFB; }
        .psr-input:focus,
        .psr-select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184,149,111,0.25);
        }
        .psr-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.625rem center;
            background-size: 1.125rem;
            padding-right: 2rem;
        }

        /* Buttons */
        .psr-btn {
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
            transition: background 150ms, opacity 150ms, transform 150ms;
            text-decoration: none;
            font-family: inherit;
        }
        .psr-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .psr-btn-outline {
            background: #fff;
            color: #475569;
            border: 1px solid var(--border);
        }
        .psr-btn-outline:hover:not(:disabled) { background: #F9FAFB; }
        .dark .psr-btn-outline { background: #1E293B; color: #CBD5E1; }
        .psr-btn-emerald { background: #059669; color: #fff; }
        .psr-btn-emerald:hover:not(:disabled) { background: #047857; }
        .psr-btn-blue { background: #2563EB; color: #fff; }
        .psr-btn-blue:hover:not(:disabled) { background: #1D4ED8; }
        .psr-btn-rose { background: #E11D48; color: #fff; }
        .psr-btn-rose:hover:not(:disabled) { background: #BE123C; }
        .psr-btn-charcoal { background: var(--charcoal); color: #fff; }
        .psr-btn-charcoal:hover:not(:disabled) { background: #1A1A1A; }
        .psr-btn-icon {
            width: 0.875rem;
            height: 0.875rem;
            stroke-width: 2;
            flex-shrink: 0;
        }

        /* ================ SUMMARY CARDS ================ */
        .psr-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) { .psr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .psr-summary-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

        .psr-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .dark .psr-stat { background: #0F172A; }

        .psr-stat-gold {
            background: linear-gradient(135deg, #B8956F, #A07E5A);
            color: #fff;
            border: 0;
            box-shadow: 0 4px 12px rgba(184,149,111,0.25);
        }
        .psr-stat-gold .psr-stat-label,
        .psr-stat-gold .psr-stat-value { color: #fff; }

        .psr-stat-label {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748B;
            margin: 0;
        }
        .psr-stat-gold .psr-stat-label { color: rgba(255,255,255,0.85); }

        .psr-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.5rem 0 0 0;
            line-height: 1.2;
        }
        .psr-stat-value.psr-stat-md { font-size: 1.125rem; }

        .psr-stat-meta {
            font-size: 0.75rem;
            color: var(--muted);
            margin: 0.25rem 0 0 0;
        }

        .psr-stat-icon {
            flex-shrink: 0;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .psr-stat-icon svg { width: 1.125rem; height: 1.125rem; }
        .psr-stat-icon-emerald { background: #D1FAE5; color: #047857; }
        .psr-stat-icon-amber { background: #FEF3C7; color: #B45309; }
        .psr-stat-icon-blue { background: #DBEAFE; color: #1D4ED8; }
        .psr-stat-icon-purple { background: #EDE9FE; color: #6D28D9; }
        .psr-stat-icon-white { background: rgba(255,255,255,0.2); color: #fff; }

        /* ================ EXPORT BAR ================ */
        .psr-export {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .psr-export {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .psr-export-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .psr-loading-text {
            margin-top: 0.75rem;
            font-size: 0.75rem;
            color: var(--muted);
            font-style: italic;
        }

        /* ================ CHARTS ================ */
        .psr-chart-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 1024px) { .psr-chart-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .psr-chart-canvas {
            position: relative;
            height: 240px;
            margin-top: 0.5rem;
        }

        /* ================ WEEKLY RECAP ================ */
        .psr-weekly-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) { .psr-weekly-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .psr-weekly-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .psr-weekly-card {
            background: var(--gold-soft);
            border: 1px solid var(--stone);
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .dark .psr-weekly-card { background: #1E293B; border-color: #334155; }

        .psr-weekly-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .psr-weekly-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.03em;
        }
        .psr-weekly-range {
            font-size: 0.625rem;
            color: var(--muted);
        }
        .psr-weekly-revenue {
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--text);
            margin: 0.5rem 0 0 0;
        }
        .psr-weekly-qty {
            font-size: 0.75rem;
            color: var(--muted);
            margin: 0.125rem 0 0 0;
        }
        .psr-weekly-foot {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border);
        }
        .psr-weekly-foot-label {
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94A3B8;
            margin: 0;
        }
        .psr-weekly-foot-name {
            font-size: 0.75rem;
            font-weight: 700;
            color: #334155;
            margin: 0.125rem 0 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .psr-weekly-foot-name { color: #E2E8F0; }

        .psr-weekly-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.875rem;
        }
        .psr-weekly-total-label { font-weight: 700; color: var(--text); }
        .psr-weekly-total-value { font-weight: 800; color: var(--gold); }

        /* ================ TABLE ================ */
        .psr-table-wrap {
            overflow-x: auto;
        }
        .psr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .psr-table thead {
            background: var(--charcoal);
            color: #fff;
        }
        .psr-table thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            white-space: nowrap;
        }
        .psr-table thead th.psr-sortable {
            cursor: pointer;
            user-select: none;
        }
        .psr-table thead th.psr-sortable:hover { background: #1A1A1A; }
        .psr-th-content {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .psr-table thead th.psr-text-right .psr-th-content { float: right; }
        .psr-table thead th.psr-text-center .psr-th-content { display: flex; justify-content: center; }

        .psr-table tbody tr {
            border-top: 1px solid #F3F4F6;
            transition: background 150ms;
        }
        .dark .psr-table tbody tr { border-top-color: #1E293B; }
        .psr-table tbody tr:hover { background: var(--gold-soft); }
        .dark .psr-table tbody tr:hover { background: #1E293B; }

        .psr-table td {
            padding: 0.75rem 1rem;
            color: #334155;
            vertical-align: middle;
        }
        .dark .psr-table td { color: #E2E8F0; }
        .psr-text-right { text-align: right; }
        .psr-text-center { text-align: center; }

        .psr-no { color: var(--muted); font-size: 0.75rem; }
        .psr-name { font-weight: 700; color: var(--text); margin: 0; }
        .psr-cat-chip {
            display: inline-block;
            background: var(--gold-tile);
            color: var(--mocha);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
        }
        .dark .psr-cat-chip { background: #334155; color: #CBD5E1; }
        .psr-qty { font-weight: 700; color: var(--text); }
        .psr-total { font-weight: 800; color: var(--gold); }
        .psr-meta-cell { font-size: 0.75rem; color: var(--muted); }

        .psr-table tfoot {
            background: var(--gold-soft);
            border-top: 2px solid rgba(184,149,111,0.4);
        }
        .dark .psr-table tfoot { background: #1E293B; }
        .psr-table tfoot td {
            font-weight: 800;
            font-size: 0.875rem;
        }

        /* Empty state */
        .psr-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .psr-empty svg { width: 2.5rem; height: 2.5rem; color: #CBD5E1; }
        .psr-empty-title { font-weight: 700; color: var(--text); font-size: 0.875rem; margin: 0; }
        .psr-empty-sub { font-size: 0.75rem; margin: 0; }

        /* Pagination */
        .psr-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .psr-page-list {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        .psr-page-btn {
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
        .psr-page-btn:hover:not(:disabled) { background: #F9FAFB; }
        .psr-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .psr-page-btn.psr-active {
            background: var(--gold);
            color: #fff;
            border-color: var(--gold);
        }
        .dark .psr-page-btn { background: #1E293B; color: #CBD5E1; border-color: #334155; }

        /* Print */
        @media print {
            body { background: #fff !important; }
            .psr-no-print { display: none !important; }
            .fi-sidebar, .fi-topbar, aside, header, nav { display: none !important; }
            .psr-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>

    <div class="psr">

        {{-- ============================ FILTERS ============================ --}}
        <div class="psr-card psr-no-print report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memperbarui data..." />
            <div class="psr-card-header">
                <div>
                    <h3 class="psr-card-title">Filter Laporan</h3>
                    <p class="psr-card-sub">Periode aktif: <strong style="color: var(--gold)">{{ $periodLabel }}</strong></p>
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
                    <button type="button" wire:click="resetFilters" class="psr-btn psr-btn-outline">
                        <svg class="psr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
                </div>
            </div>

            <div class="psr-filter-grid">
                <div>
                    <label class="psr-label">Bulan</label>
                    <select wire:model.live="filter_month" class="psr-select">
                        @foreach($monthOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="psr-label">Tahun</label>
                    <select wire:model.live="filter_year" class="psr-select">
                        @foreach($yearOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="psr-label">Minggu</label>
                    <select wire:model.live="filter_week" class="psr-select">
                        @foreach($weekOptions as $val => $label)
                            <option value="{{ $val === '' ? '' : $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="psr-label">Status Pembayaran</label>
                    <select wire:model.live="filter_payment_status" class="psr-select">
                        @foreach($paymentStatusOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="psr-label">Kategori Produk</label>
                    <select wire:model.live="filter_category_id" class="psr-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categoriesOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="psr-col-2">
                    <label class="psr-label">Nama Produk</label>
                    <select wire:model.live="filter_product_id" class="psr-select">
                        <option value="">Semua Produk</option>
                        @foreach($productsOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="psr-label">Cari</label>
                    <input type="search"
                           wire:model.live.debounce.500ms="filter_search"
                           placeholder="Nama / kategori produk"
                           class="psr-input">
                </div>
            </div>
        </div>

        {{-- ============================ SUMMARY CARDS ============================ --}}
        <div class="psr-summary-grid">
            {{-- Total Pendapatan --}}
            <div class="psr-stat psr-stat-gold">
                <div style="flex:1;">
                    <p class="psr-stat-label">Total Pendapatan</p>
                    <p class="psr-stat-value">{{ $rupiah($summary['total_revenue']) }}</p>
                </div>
                <div class="psr-stat-icon psr-stat-icon-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>

            {{-- Total Produk Terjual --}}
            <div class="psr-stat">
                <div style="flex:1;">
                    <p class="psr-stat-label">Produk Terjual</p>
                    <p class="psr-stat-value">{{ number_format($summary['total_qty'], 0, ',', '.') }} <span style="font-size:0.875rem;color:#94A3B8;font-weight:500;">pcs</span></p>
                </div>
                <div class="psr-stat-icon psr-stat-icon-emerald">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>

            {{-- Produk Terlaris --}}
            <div class="psr-stat">
                <div style="flex:1;min-width:0;">
                    <p class="psr-stat-label">Produk Terlaris</p>
                    <p class="psr-stat-value psr-stat-md" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $summary['best_seller']['name'] ?? '' }}">
                        {{ $summary['best_seller']['name'] ?? '—' }}
                    </p>
                    <p class="psr-stat-meta">{{ $summary['best_seller'] ? number_format($summary['best_seller']['qty'], 0, ',', '.') . ' pcs terjual' : 'Belum ada data' }}</p>
                </div>
                <div class="psr-stat-icon psr-stat-icon-amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>

            {{-- Jumlah Transaksi --}}
            <div class="psr-stat">
                <div style="flex:1;">
                    <p class="psr-stat-label">Jumlah Transaksi</p>
                    <p class="psr-stat-value">{{ number_format($summary['total_transactions'], 0, ',', '.') }}</p>
                </div>
                <div class="psr-stat-icon psr-stat-icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>

            {{-- Rata-rata per Minggu --}}
            <div class="psr-stat">
                <div style="flex:1;">
                    <p class="psr-stat-label">Rata-rata / Minggu</p>
                    <p class="psr-stat-value psr-stat-md">{{ $rupiah($summary['average_per_week']) }}</p>
                </div>
                <div class="psr-stat-icon psr-stat-icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ============================ EXPORT BAR ============================ --}}
        <div class="psr-card psr-no-print">
            <div class="psr-export">
                <div>
                    <p style="margin:0;font-weight:700;color:var(--text);font-size:0.875rem;">Ekspor Laporan</p>
                    <p style="margin:0.25rem 0 0 0;font-size:0.75rem;color:var(--muted);">Mengikuti filter aktif. Total {{ number_format($totalRows, 0, ',', '.') }} produk.</p>
                </div>
                <div class="psr-export-buttons">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="psr-btn psr-btn-emerald">
                        <span wire:loading.remove wire:target="exportExcel" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="psr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Excel
                        </span>
                        <span wire:loading.flex wire:target="exportExcel" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="psr-btn psr-btn-blue">
                        <span wire:loading.remove wire:target="exportCsv" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="psr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            CSV
                        </span>
                        <span wire:loading.flex wire:target="exportCsv" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button wire:click="exportPdf" wire:loading.attr="disabled" wire:target="exportPdf" class="psr-btn psr-btn-rose">
                        <span wire:loading.remove wire:target="exportPdf" style="display:inline-flex;align-items:center;gap:0.375rem;">
                            <svg class="psr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </span>
                        <span wire:loading.flex wire:target="exportPdf" style="align-items:center;gap:0.375rem;">
                            <span class="report-btn-spinner"></span> Menyiapkan...
                        </span>
                    </button>
                    <button onclick="window.print()" class="psr-btn psr-btn-charcoal">
                        <svg class="psr-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>
            <div wire:loading wire:target="exportExcel,exportCsv,exportPdf" class="psr-loading-text">
                Memproses ekspor...
            </div>
        </div>

        {{-- ============================ CHARTS ============================ --}}
        <div class="psr-chart-grid report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat grafik..." />
            <div class="psr-card">
                <h3 class="psr-card-title">Pendapatan per Minggu</h3>
                <p class="psr-card-sub">Total revenue produk per minggu</p>
                <div class="psr-chart-canvas">
                    <canvas id="chart-revenue"
                            data-labels='@json($chartRevenue['labels'])'
                            data-values='@json($chartRevenue['data'])'></canvas>
                </div>
            </div>

            <div class="psr-card">
                <h3 class="psr-card-title">Top 5 Produk Terlaris</h3>
                <p class="psr-card-sub">Berdasarkan jumlah terjual</p>
                <div class="psr-chart-canvas">
                    <canvas id="chart-top-products"
                            data-labels='@json($chartTopProducts['labels'])'
                            data-values='@json($chartTopProducts['data'])'></canvas>
                </div>
            </div>

            <div class="psr-card">
                <h3 class="psr-card-title">Jumlah Terjual per Minggu</h3>
                <p class="psr-card-sub">Akumulasi qty per minggu</p>
                <div class="psr-chart-canvas">
                    <canvas id="chart-qty"
                            data-labels='@json($chartQty['labels'])'
                            data-values='@json($chartQty['data'])'></canvas>
                </div>
            </div>
        </div>

        {{-- ============================ WEEKLY RECAP ============================ --}}
        <div class="psr-card report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat rekap mingguan..." />
            <div class="psr-card-header">
                <div>
                    <h3 class="psr-card-title">Rekap Mingguan</h3>
                    <p class="psr-card-sub">Pembagian 4 minggu dalam bulan {{ $monthOptions[$this->filter_month] ?? '' }} {{ $this->filter_year }}</p>
                </div>
            </div>
            <div class="psr-weekly-grid">
                @foreach($weeks as $w)
                    <div class="psr-weekly-card">
                        <div class="psr-weekly-head">
                            <span class="psr-weekly-label">{{ $w['label'] }}</span>
                            <span class="psr-weekly-range">{{ $w['range'] }}</span>
                        </div>
                        <p class="psr-weekly-revenue">{{ $rupiah($w['total_revenue']) }}</p>
                        <p class="psr-weekly-qty">{{ number_format($w['total_qty'], 0, ',', '.') }} produk terjual</p>
                        <div class="psr-weekly-foot">
                            <p class="psr-weekly-foot-label">Terlaris</p>
                            <p class="psr-weekly-foot-name" title="{{ $w['best_product'] ?? '' }}">
                                {{ $w['best_product'] ?? '—' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="psr-weekly-total">
                <span class="psr-weekly-total-label">Total Penjualan Bulanan</span>
                <span class="psr-weekly-total-value">{{ $rupiah($weeks->sum('total_revenue')) }} • {{ number_format($weeks->sum('total_qty'), 0, ',', '.') }} pcs</span>
            </div>
        </div>

        {{-- ============================ TABLE ============================ --}}
        <div class="psr-card psr-card-flush report-section-loadable">
            <x-report-loading :target="$loadingTargets" text="Memuat tabel laporan..." />
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;flex-wrap:wrap;">
                <div>
                    <h3 class="psr-card-title">Detail Penjualan Produk</h3>
                    <p class="psr-card-sub">Total {{ number_format($totalRows, 0, ',', '.') }} produk pada periode aktif</p>
                </div>
                <div class="psr-no-print" style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-size:0.75rem;color:var(--muted);">Per halaman</label>
                    <select wire:model.live="per_page" class="psr-select" style="width:auto;padding:0.25rem 2rem 0.25rem 0.625rem;font-size:0.75rem;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="psr-table-wrap">
                <table class="psr-table">
                    <thead>
                        <tr>
                            <th style="width:3rem;">No</th>
                            <th class="psr-sortable" wire:click="sortBy('name')">
                                <span class="psr-th-content">Nama Produk @include('filament.pages.partials.sort-icon', ['field' => 'name'])</span>
                            </th>
                            <th class="psr-sortable" wire:click="sortBy('category')">
                                <span class="psr-th-content">Kategori @include('filament.pages.partials.sort-icon', ['field' => 'category'])</span>
                            </th>
                            <th class="psr-text-right psr-sortable" wire:click="sortBy('price')">
                                <span class="psr-th-content">Harga @include('filament.pages.partials.sort-icon', ['field' => 'price'])</span>
                            </th>
                            <th class="psr-text-center psr-sortable" wire:click="sortBy('qty')">
                                <span class="psr-th-content">Jumlah @include('filament.pages.partials.sort-icon', ['field' => 'qty'])</span>
                            </th>
                            <th class="psr-text-right psr-sortable" wire:click="sortBy('total')">
                                <span class="psr-th-content">Total @include('filament.pages.partials.sort-icon', ['field' => 'total'])</span>
                            </th>
                            <th>Minggu</th>
                            <th>Periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $r)
                            <tr>
                                <td class="psr-no">{{ ($this->page - 1) * $this->per_page + $i + 1 }}</td>
                                <td><p class="psr-name">{{ $r['name'] }}</p></td>
                                <td><span class="psr-cat-chip">{{ $r['category'] }}</span></td>
                                <td class="psr-text-right">{{ $rupiah($r['price']) }}</td>
                                <td class="psr-text-center psr-qty">{{ number_format($r['qty'], 0, ',', '.') }}</td>
                                <td class="psr-text-right psr-total">{{ $rupiah($r['total']) }}</td>
                                <td class="psr-meta-cell">{{ $r['week_label'] }}</td>
                                <td class="psr-meta-cell">{{ $r['period_label'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="psr-empty">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="psr-empty-title">Tidak ada data penjualan produk</p>
                                        <p class="psr-empty-sub">Coba ubah filter atau periode laporan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalRows > 0)
                        <tfoot>
                            <tr>
                                <td colspan="4" class="psr-text-right" style="color:#475569;">TOTAL KESELURUHAN</td>
                                <td class="psr-text-center">{{ number_format($tableTotals['total_qty'], 0, ',', '.') }}</td>
                                <td class="psr-text-right" style="color:var(--gold);">{{ $rupiah($tableTotals['total_revenue']) }}</td>
                                <td colspan="2" style="font-size:0.75rem;color:var(--muted);font-style:italic;font-weight:400;">{{ $tableTotals['product_count'] }} produk</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if($totalPages > 1)
                <div class="psr-pagination psr-no-print">
                    <p style="color:var(--muted);margin:0;">
                        Halaman <strong style="color:var(--text);">{{ $this->page }}</strong> dari <strong style="color:var(--text);">{{ $totalPages }}</strong>
                    </p>
                    <div class="psr-page-list">
                        <button wire:click="previousPage" @disabled($this->page <= 1) class="psr-page-btn">← Sebelumnya</button>
                        @for($p = max(1, $this->page - 2); $p <= min($totalPages, $this->page + 2); $p++)
                            <button wire:click="gotoPage({{ $p }})" class="psr-page-btn {{ $p === $this->page ? 'psr-active' : '' }}">{{ $p }}</button>
                        @endfor
                        <button wire:click="nextPage" @disabled($this->page >= $totalPages) class="psr-page-btn">Selanjutnya →</button>
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

                function renderCharts() {
                    if (typeof Chart === 'undefined') return;

                    document.querySelectorAll('.psr canvas').forEach(c => {
                        const existing = Chart.getChart(c);
                        if (existing) existing.destroy();
                    });

                    const goldGradient = (ctx) => {
                        const g = ctx.createLinearGradient(0, 0, 0, 240);
                        g.addColorStop(0, 'rgba(184,149,111,0.8)');
                        g.addColorStop(1, 'rgba(184,149,111,0.1)');
                        return g;
                    };

                    const cv1 = document.getElementById('chart-revenue');
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

                    const cv2 = document.getElementById('chart-top-products');
                    if (cv2) {
                        const labels = JSON.parse(cv2.dataset.labels || '[]');
                        const values = JSON.parse(cv2.dataset.values || '[]');
                        new Chart(cv2, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Jumlah Terjual',
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

                    const cv3 = document.getElementById('chart-qty');
                    if (cv3) {
                        const labels = JSON.parse(cv3.dataset.labels || '[]');
                        const values = JSON.parse(cv3.dataset.values || '[]');
                        new Chart(cv3, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Qty Terjual',
                                    data: values,
                                    backgroundColor: '#3B3529',
                                    borderRadius: 8,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                            },
                        });
                    }
                }

                document.addEventListener('DOMContentLoaded', renderCharts);
                document.addEventListener('livewire:navigated', renderCharts);
                if (window.Livewire) {
                    window.Livewire.hook('morph.updated', renderCharts);
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
