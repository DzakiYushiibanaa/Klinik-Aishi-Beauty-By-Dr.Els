{{-- Partial: Stats Cards --}}
    {{-- Stats Cards --}}
    <div class="dash-stats">
        {{-- Janji Temu Aktif — gold --}}
        <div class="dash-stat dash-stat--gold">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Aktif</span>
            </div>
            <p class="dash-stat__value">{{ $stats['upcoming'] }}</p>
            <p class="dash-stat__label">Janji Temu Aktif</p>
            <p class="dash-stat__hint">Jadwal menunggu &amp; disetujui</p>
        </div>

        {{-- Pesanan Aktif — amber/orange --}}
        <div class="dash-stat dash-stat--amber">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Diproses</span>
            </div>
            <p class="dash-stat__value">{{ $stats['active_orders'] }}</p>
            <p class="dash-stat__label">Pesanan Aktif</p>
            <p class="dash-stat__hint">Menunggu bayar &amp; diproses</p>
        </div>

        {{-- Kunjungan Selesai — mint/emerald --}}
        <div class="dash-stat dash-stat--mint">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Total</span>
            </div>
            <p class="dash-stat__value">{{ $stats['total_rekam_medis'] }}</p>
            <p class="dash-stat__label">Kunjungan Selesai</p>
            <p class="dash-stat__hint">Total riwayat perawatan</p>
        </div>
    </div>
