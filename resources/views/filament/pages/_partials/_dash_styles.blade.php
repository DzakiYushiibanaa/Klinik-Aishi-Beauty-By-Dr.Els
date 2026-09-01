@once
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
        @media (min-width: 1024px) { .dash-chart-grid { grid-template-columns: 1fr 1fr; } }

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
@endonce