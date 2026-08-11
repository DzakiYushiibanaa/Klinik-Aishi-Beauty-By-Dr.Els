        {{-- ============ 3. SUMMARY CARDS ============ --}}
        <div class="dash-stats-grid">
            <div class="dash-stat dash-stat-gold">
                <p class="dash-stat-label">Pendapatan Hari Ini</p>
                <p class="dash-stat-value">{{ $rupiah($stats['revenue_today']) }}</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Janji Temu</p>
                <p class="dash-stat-value">{{ $stats['appointment_today'] }}<span class="dash-stat-unit">temu</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Pasien Hari Ini</p>
                <p class="dash-stat-value">{{ $stats['patient_today'] }}<span class="dash-stat-unit">pasien</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Produk Terjual</p>
                <p class="dash-stat-value">{{ $stats['product_sold_today'] }}<span class="dash-stat-unit">pcs</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Treatment Selesai</p>
                <p class="dash-stat-value">{{ $stats['treatment_done_today'] }}<span class="dash-stat-unit">sesi</span></p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat-label">Pending Payment</p>
                <p class="dash-stat-value">{{ $stats['pending_payment'] }}<span class="dash-stat-unit">tx</span></p>
            </div>
        </div>
