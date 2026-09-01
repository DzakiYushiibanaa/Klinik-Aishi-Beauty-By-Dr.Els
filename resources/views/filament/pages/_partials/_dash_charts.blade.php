        {{-- ============ 5. CHARTS ============ --}}
        <div class="dash-chart-grid">
            <div class="dash-card">
                <h3 class="dash-card-title">Pendapatan 7 Hari Terakhir</h3>
                <p class="dash-card-sub">Trend pemasukan harian klinik</p>
                <div class="dash-chart-canvas">
                    <canvas id="dash-chart-revenue"
                            data-labels='@json($chartRevenue['labels'])'
                            data-values='@json($chartRevenue['data'])'></canvas>
                </div>
            </div>

            <div class="dash-card">
                <h3 class="dash-card-title">Appointment 7 Hari Terakhir</h3>
                <p class="dash-card-sub">Volume janji temu harian</p>
                <div class="dash-chart-canvas">
                    <canvas id="dash-chart-appointments"
                            data-labels='@json($chartAppointments['labels'])'
                            data-values='@json($chartAppointments['data'])'></canvas>
                </div>
            </div>
        </div>