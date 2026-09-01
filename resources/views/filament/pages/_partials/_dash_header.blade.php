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

            @if (auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                <div class="dash-actions">
                    <a href="{{ $apptUrl }}" class="dash-action-btn dash-action-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Booking
                    </a>
                    <a href="{{ $patientUrl }}" class="dash-action-btn">Pasien</a>
                    <a href="{{ $productUrl }}" class="dash-action-btn">Produk</a>
                    <a href="{{ $treatmentUrl }}" class="dash-action-btn">Treatment</a>
                </div>
            @endif
        </div>
