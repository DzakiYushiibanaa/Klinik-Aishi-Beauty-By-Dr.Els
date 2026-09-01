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
