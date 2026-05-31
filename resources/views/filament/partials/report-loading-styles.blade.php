{{-- Loading state styles dipakai bersama oleh semua halaman laporan.
     Di-push ke stack 'styles' supaya tidak menambah root element ke Livewire component. --}}
@once
@push('styles')
<style>
    /* Container yang berisi <x-report-loading /> harus position:relative agar
       overlay menempel ke card. */

    .report-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 30;
        display: none; /* Livewire akan ubah ke flex saat loading */
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        border-radius: inherit;
        animation: report-fade-in 150ms ease;
        cursor: wait;
    }
    .dark .report-loading-overlay {
        background: rgba(15, 23, 42, 0.7);
    }
    @keyframes report-fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .report-loading-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 1.125rem;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 9999px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 700;
        color: #2C2622;
        white-space: nowrap;
    }
    .dark .report-loading-content {
        background: #1E293B;
        border-color: #334155;
        color: #F9FAFB;
    }

    .report-spinner {
        width: 1.125rem;
        height: 1.125rem;
        border: 2.5px solid #E8E0D8;
        border-top-color: #B8956F;
        border-radius: 9999px;
        animation: report-spin 0.7s linear infinite;
        flex-shrink: 0;
    }
    .dark .report-spinner {
        border-color: #334155;
        border-top-color: #B8956F;
    }
    @keyframes report-spin {
        to { transform: rotate(360deg); }
    }

    /* Saat loading aktif, child di dalam container yang punya .report-section-loadable
       akan otomatis turun opacity nya. Hanya berlaku saat overlay sedang flex. */
    .report-section-loadable {
        position: relative;
        transition: opacity 200ms ease, filter 200ms ease;
    }
    .report-section-loadable:has(.report-loading-overlay[style*="display: flex"]) > :not(.report-loading-overlay) {
        opacity: 0.6;
        filter: blur(0.5px);
        pointer-events: none;
    }

    /* Filter status badge — kanan-atas pada card filter */
    .report-filter-status {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.3125rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        font-family: 'Plus Jakarta Sans', sans-serif;
        white-space: nowrap;
        transition: all 200ms ease;
    }
    .report-filter-status-idle {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .dark .report-filter-status-idle {
        background: rgba(4, 120, 87, 0.15);
        color: #6EE7B7;
        border-color: rgba(4, 120, 87, 0.4);
    }
    .report-filter-status-loading {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .dark .report-filter-status-loading {
        background: rgba(180, 83, 9, 0.15);
        color: #FCD34D;
        border-color: rgba(180, 83, 9, 0.4);
    }
    .report-filter-status-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: currentColor;
        flex-shrink: 0;
    }
    .report-filter-status-loading .report-filter-status-dot {
        animation: report-pulse 1s ease infinite;
    }
    @keyframes report-pulse {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.4); }
    }

    /* Skeleton untuk angka summary card */
    .report-summary-loadable {
        position: relative;
        transition: opacity 200ms ease;
    }
    .report-summary-loadable:has(.report-skeleton-overlay[style*="display: block"]) > :not(.report-skeleton-overlay) {
        opacity: 0.45;
    }

    /* Spinner kecil pada tombol export saat sedang diproses */
    .report-btn-spinner {
        display: inline-block;
        width: 0.875rem;
        height: 0.875rem;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #FFFFFF;
        border-radius: 9999px;
        animation: report-spin 0.7s linear infinite;
        flex-shrink: 0;
    }
</style>
@endpush
@endonce
