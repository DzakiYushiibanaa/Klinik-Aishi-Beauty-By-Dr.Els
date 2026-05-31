<style>
    /* Hide Filament default global search bar (we replace it with our own modal). */
    .fi-global-search-ctn,
    .fi-global-search,
    .fi-global-search-field {
        display: none !important;
    }

    /* ================ TRIGGER PILL (in topbar) ================ */
    .aishi-search-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        height: 2.375rem;
        padding: 0 0.5rem 0 1rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #B5ADA4;
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 200ms ease;
        font-family: 'Plus Jakarta Sans', sans-serif;
        width: 100%;
        max-width: 360px;
        margin-right: 0.25rem;
    }
    .aishi-search-trigger:hover {
        background: rgba(184, 149, 111, 0.1);
        border-color: rgba(184, 149, 111, 0.35);
        color: #FAF9F6;
    }
    .aishi-search-trigger:focus-visible {
        outline: none;
        background: rgba(184, 149, 111, 0.12);
        border-color: rgba(184, 149, 111, 0.5);
        box-shadow: 0 0 0 3px rgba(184, 149, 111, 0.2);
        color: #FAF9F6;
    }
    .aishi-search-trigger svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        color: #8A8078;
        transition: color 200ms;
    }
    .aishi-search-trigger:hover svg { color: #C8B89E; }
    .aishi-search-trigger-text {
        flex: 1;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: 0.005em;
    }
    .aishi-search-trigger-kbd {
        display: inline-flex;
        align-items: center;
        gap: 0.125rem;
        padding: 0.1875rem 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 0.375rem;
        font-size: 0.6875rem;
        font-weight: 600;
        font-family: ui-monospace, "SF Mono", monospace;
        color: #9A8F84;
        background: rgba(0, 0, 0, 0.18);
        flex-shrink: 0;
        line-height: 1;
        letter-spacing: 0.02em;
    }
    @media (max-width: 1024px) {
        .aishi-search-trigger { max-width: 240px; }
        .aishi-search-trigger-kbd { display: none; }
    }
    @media (max-width: 768px) {
        .aishi-search-trigger {
            max-width: none;
            width: 2.375rem;
            padding: 0;
            justify-content: center;
        }
        .aishi-search-trigger-text { display: none; }
    }

    /* ================ COMMAND PALETTE MODAL ================ */
    .aishi-search-overlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(28, 22, 18, 0.55);
        backdrop-filter: blur(6px);
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding: 4rem 1rem 1rem;
        animation: aishi-fade-in 150ms ease;
    }
    .aishi-search-overlay[data-open="true"] {
        display: flex;
    }
    @keyframes aishi-fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes aishi-slide-up {
        from { opacity: 0; transform: translateY(-16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .aishi-search-modal {
        background: #FFFFFF;
        border-radius: 1.25rem;
        width: 100%;
        max-width: 640px;
        max-height: 70vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 24px 60px -12px rgba(28, 22, 18, 0.5);
        border: 1px solid rgba(184, 149, 111, 0.25);
        animation: aishi-slide-up 200ms ease;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .dark .aishi-search-modal { background: #1E1814; border-color: rgba(184, 149, 111, 0.4); }

    /* Mobile fullscreen */
    @media (max-width: 640px) {
        .aishi-search-overlay { padding: 0; }
        .aishi-search-modal {
            max-width: 100%;
            max-height: 100vh;
            height: 100vh;
            border-radius: 0;
        }
    }

    /* Input row */
    .aishi-search-input-row {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.125rem 1.375rem;
        border-bottom: 1px solid #EDE8E3;
    }
    .dark .aishi-search-input-row { border-bottom-color: #332C27; }
    .aishi-search-input-row .icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #8C7A63;
        flex-shrink: 0;
    }
    .aishi-search-input {
        flex: 1;
        border: 0;
        outline: 0;
        background: transparent;
        color: #1A1A1A;
        font-size: 1rem;
        font-weight: 500;
        font-family: inherit;
        padding: 0;
    }
    .dark .aishi-search-input { color: #FAF9F6; }
    .aishi-search-input::placeholder {
        color: #94867A;
    }
    .aishi-search-close {
        background: transparent;
        border: 0;
        cursor: pointer;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        color: #8C7A63;
        font-size: 0.6875rem;
        font-weight: 700;
        font-family: ui-monospace, monospace;
        border: 1px solid #EDE8E3;
        flex-shrink: 0;
    }
    .aishi-search-close:hover { background: #FAF8F5; color: #1A1A1A; }
    .dark .aishi-search-close { border-color: #332C27; color: #C8C0B8; }
    .dark .aishi-search-close:hover { background: #2A211C; color: #FAF9F6; }

    /* Body */
    .aishi-search-body {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
    }
    .aishi-search-body::-webkit-scrollbar { width: 8px; }
    .aishi-search-body::-webkit-scrollbar-track { background: transparent; }
    .aishi-search-body::-webkit-scrollbar-thumb { background: #E8E0D8; border-radius: 8px; }
    .dark .aishi-search-body::-webkit-scrollbar-thumb { background: #332C27; }

    /* Empty / loading / no result states */
    .aishi-search-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #8C7A63;
    }
    .aishi-search-state svg {
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto 0.875rem;
        color: #D6C8B8;
    }
    .aishi-search-state-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #1A1A1A;
        margin: 0;
    }
    .dark .aishi-search-state-title { color: #FAF9F6; }
    .aishi-search-state-sub {
        font-size: 0.8125rem;
        color: #8C7A63;
        margin: 0.375rem 0 0 0;
    }

    /* Group */
    .aishi-search-group {
        padding: 0.625rem 0.5rem 0.5rem;
    }
    .aishi-search-group + .aishi-search-group {
        border-top: 1px solid #F2EFE9;
        margin-top: 0.25rem;
    }
    .dark .aishi-search-group + .aishi-search-group { border-top-color: #2A211C; }
    .aishi-search-group-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #B8956F;
        padding: 0.375rem 0.75rem;
        margin: 0;
    }

    /* Item */
    .aishi-search-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.625rem;
        text-decoration: none;
        cursor: pointer;
        transition: background 120ms;
    }
    .aishi-search-item:hover,
    .aishi-search-item.is-active {
        background: #FAF8F5;
    }
    .dark .aishi-search-item:hover,
    .dark .aishi-search-item.is-active { background: #2A211C; }

    .aishi-search-item-avatar {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        background: linear-gradient(135deg, #B8956F, #A07E5A);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
    }
    .aishi-search-item-body {
        flex: 1;
        min-width: 0;
    }
    .aishi-search-item-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #1A1A1A;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .aishi-search-item-title { color: #FAF9F6; }
    .aishi-search-item-title mark {
        background: rgba(184, 149, 111, 0.25);
        color: inherit;
        padding: 0 2px;
        border-radius: 3px;
    }
    .aishi-search-item-sub {
        font-size: 0.8125rem;
        color: #8C7A63;
        margin: 0.125rem 0 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .aishi-search-item-badge {
        flex-shrink: 0;
        padding: 0.1875rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        background: #F2EFE9;
        color: #8C7A63;
    }
    .dark .aishi-search-item-badge { background: #332C27; color: #C8C0B8; }
    .aishi-search-item-arrow {
        width: 0.875rem;
        height: 0.875rem;
        color: #B8956F;
        opacity: 0;
        transition: opacity 150ms;
        flex-shrink: 0;
    }
    .aishi-search-item:hover .aishi-search-item-arrow,
    .aishi-search-item.is-active .aishi-search-item-arrow {
        opacity: 1;
    }

    /* Footer hint */
    .aishi-search-footer {
        padding: 0.625rem 1.25rem;
        border-top: 1px solid #EDE8E3;
        background: #FAF8F5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #8C7A63;
        flex-wrap: wrap;
    }
    .dark .aishi-search-footer { background: #2A211C; border-top-color: #332C27; color: #C8C0B8; }
    .aishi-search-footer-keys {
        display: inline-flex;
        gap: 0.875rem;
        flex-wrap: wrap;
    }
    .aishi-search-footer-key {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .aishi-search-footer kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.25rem;
        padding: 0.125rem 0.375rem;
        font-family: ui-monospace, monospace;
        font-size: 0.6875rem;
        font-weight: 700;
        background: #fff;
        border: 1px solid #E8E0D8;
        border-radius: 0.25rem;
        color: #1A1A1A;
    }
    .dark .aishi-search-footer kbd { background: #1E1814; border-color: #332C27; color: #FAF9F6; }

    @media (max-width: 480px) {
        .aishi-search-footer { display: none; }
    }

    /* Loading skeleton */
    .aishi-skeleton {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.75rem;
    }
    .aishi-skeleton-avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        background: linear-gradient(90deg, #F2EFE9 25%, #FAF8F5 50%, #F2EFE9 75%);
        background-size: 200% 100%;
        animation: aishi-shimmer 1.5s infinite;
    }
    .aishi-skeleton-body { flex: 1; }
    .aishi-skeleton-line {
        height: 0.625rem;
        border-radius: 0.25rem;
        background: linear-gradient(90deg, #F2EFE9 25%, #FAF8F5 50%, #F2EFE9 75%);
        background-size: 200% 100%;
        animation: aishi-shimmer 1.5s infinite;
        margin-bottom: 0.375rem;
    }
    .aishi-skeleton-line.short { width: 60%; }
    .dark .aishi-skeleton-avatar,
    .dark .aishi-skeleton-line {
        background: linear-gradient(90deg, #2A211C 25%, #332C27 50%, #2A211C 75%);
        background-size: 200% 100%;
    }
    @keyframes aishi-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>

{{-- Modal command palette --}}
<div id="aishiSearchOverlay"
     class="aishi-search-overlay"
     data-open="false"
     role="dialog"
     aria-modal="true"
     aria-label="Pencarian global">
    <div class="aishi-search-modal" id="aishiSearchModal">
        <div class="aishi-search-input-row">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="aishiSearchInput"
                   type="text"
                   class="aishi-search-input"
                   placeholder="Cari pasien, treatment, produk, transaksi..."
                   autocomplete="off"
                   spellcheck="false"
                   aria-label="Kata kunci pencarian">
            <button type="button" class="aishi-search-close" onclick="window.aishiSearch.close()" aria-label="Tutup">ESC</button>
        </div>

        <div class="aishi-search-body" id="aishiSearchBody">
            {{-- Initial state --}}
            <div class="aishi-search-state" id="aishiSearchEmpty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="aishi-search-state-title">Mulai ketik untuk mencari data...</p>
                <p class="aishi-search-state-sub">Pasien, treatment, produk, transaksi, menu, dan lainnya.</p>
            </div>
        </div>

        <div class="aishi-search-footer">
            <div class="aishi-search-footer-keys">
                <span class="aishi-search-footer-key"><kbd>↑</kbd><kbd>↓</kbd> Navigasi</span>
                <span class="aishi-search-footer-key"><kbd>↵</kbd> Buka</span>
                <span class="aishi-search-footer-key"><kbd>Esc</kbd> Tutup</span>
            </div>
            <span>Aishi Beauty Search</span>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.aishiSearch && window.aishiSearch.__init) return;

    const ENDPOINT = "{{ route('admin.global-search') }}";
    const STORAGE_KEY = 'aishi_recent_search';

    const overlay = document.getElementById('aishiSearchOverlay');
    const modal = document.getElementById('aishiSearchModal');
    const input = document.getElementById('aishiSearchInput');
    const body = document.getElementById('aishiSearchBody');

    let activeIndex = -1;
    let currentItems = [];
    let lastQuery = '';
    let debounceId = null;
    let abortCtrl = null;

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[m]);
    }
    function highlight(text, q) {
        if (!q) return escapeHtml(text);
        const safe = escapeHtml(text);
        const safeQ = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp(`(${safeQ})`, 'gi'), '<mark>$1</mark>');
    }

    function open() {
        overlay.dataset.open = 'true';
        document.body.style.overflow = 'hidden';
        setTimeout(() => input.focus(), 50);
    }
    function close() {
        overlay.dataset.open = 'false';
        document.body.style.overflow = '';
        input.value = '';
        renderEmpty();
        activeIndex = -1;
    }
    function renderEmpty() {
        body.innerHTML = `
            <div class="aishi-search-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="aishi-search-state-title">Mulai ketik untuk mencari data...</p>
                <p class="aishi-search-state-sub">Pasien, treatment, produk, transaksi, menu, dan lainnya.</p>
            </div>
        `;
        currentItems = [];
    }
    function renderLoading() {
        let html = '<div class="aishi-search-group">';
        for (let i = 0; i < 4; i++) {
            html += `
                <div class="aishi-skeleton">
                    <div class="aishi-skeleton-avatar"></div>
                    <div class="aishi-skeleton-body">
                        <div class="aishi-skeleton-line"></div>
                        <div class="aishi-skeleton-line short"></div>
                    </div>
                </div>
            `;
        }
        html += '</div>';
        body.innerHTML = html;
    }
    function renderNoResults() {
        body.innerHTML = `
            <div class="aishi-search-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="aishi-search-state-title">Data tidak ditemukan</p>
                <p class="aishi-search-state-sub">Coba kata kunci lain atau periksa ejaannya.</p>
            </div>
        `;
        currentItems = [];
    }
    function renderResults(data, q) {
        if (!data.groups || !data.groups.length) {
            renderNoResults();
            return;
        }

        let html = '';
        currentItems = [];
        let idx = 0;

        for (const group of data.groups) {
            html += `<div class="aishi-search-group">
                        <p class="aishi-search-group-label">${escapeHtml(group.type)}</p>`;
            for (const item of group.items) {
                const badge = item.badge
                    ? `<span class="aishi-search-item-badge">${escapeHtml(item.badge)}</span>`
                    : '';
                html += `
                    <a class="aishi-search-item" data-idx="${idx}" href="${escapeHtml(item.url)}">
                        <div class="aishi-search-item-avatar">${escapeHtml(item.initial || '?')}</div>
                        <div class="aishi-search-item-body">
                            <p class="aishi-search-item-title">${highlight(item.title, q)}</p>
                            <p class="aishi-search-item-sub">${highlight(item.subtitle || '', q)}</p>
                        </div>
                        ${badge}
                        <svg class="aishi-search-item-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                `;
                currentItems.push(item);
                idx++;
            }
            html += '</div>';
        }
        body.innerHTML = html;
        activeIndex = -1;
        setActive(0);
    }
    function setActive(i) {
        const items = body.querySelectorAll('.aishi-search-item');
        if (!items.length) { activeIndex = -1; return; }
        if (i < 0) i = items.length - 1;
        if (i >= items.length) i = 0;
        items.forEach((el, k) => el.classList.toggle('is-active', k === i));
        activeIndex = i;
        items[i].scrollIntoView({ block: 'nearest' });
    }
    function fetchResults(q) {
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        renderLoading();
        fetch(`${ENDPOINT}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            signal: abortCtrl.signal,
        })
            .then(r => r.json())
            .then(data => {
                if (q !== lastQuery) return; // race guard
                renderResults(data, q);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                renderNoResults();
            });
    }

    function onInput() {
        const q = input.value.trim();
        lastQuery = q;
        clearTimeout(debounceId);
        if (q.length < 2) {
            renderEmpty();
            return;
        }
        debounceId = setTimeout(() => fetchResults(q), 300);
    }

    function onKeyDown(e) {
        if (e.key === 'Escape') {
            close();
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentItems.length) setActive(activeIndex + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentItems.length) setActive(activeIndex - 1);
        } else if (e.key === 'Enter') {
            const items = body.querySelectorAll('.aishi-search-item');
            if (items.length && activeIndex >= 0) {
                e.preventDefault();
                items[activeIndex].click();
            }
        }
    }

    // Global keyboard shortcut Ctrl/Cmd + K
    function onGlobalKey(e) {
        const ck = e.key === 'k' || e.key === 'K';
        if ((e.ctrlKey || e.metaKey) && ck) {
            e.preventDefault();
            if (overlay.dataset.open === 'true') close();
            else open();
        }
    }

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });
    input.addEventListener('input', onInput);
    input.addEventListener('keydown', onKeyDown);
    document.addEventListener('keydown', onGlobalKey);

    window.aishiSearch = { open, close, __init: true };
})();
</script>
