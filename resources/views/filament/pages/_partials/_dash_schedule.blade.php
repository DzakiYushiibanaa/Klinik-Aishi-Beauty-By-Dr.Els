        {{-- ============ 4. JADWAL HARI INI ============ --}}
        <div class="dash-card" style="margin-bottom: 1.5rem;">
            <div class="dash-card-header-row">
                <div>
                    <h3 class="dash-card-title">Jadwal Hari Ini</h3>
                    <p class="dash-card-sub">{{ count($todaySchedule) }} appointment terjadwal</p>
                </div>
                <a href="{{ $allScheduleUrl }}" class="dash-link-all">
                    Lihat Semua
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @if(empty($todaySchedule))
                <div class="dash-empty">Belum ada jadwal untuk hari ini.</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="dash-row-table">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Pasien</th>
                                <th>Layanan</th>
                                <th>Petugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todaySchedule as $s)
                                <tr>
                                    <td><span class="dash-time-chip">{{ $s['time'] }}</span></td>
                                    <td style="font-weight:600;color:var(--text);">{{ $s['pasien'] }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($s['service'], 30) }}</td>
                                    <td style="color:var(--muted);">{{ $s['petugas'] }}</td>
                                    <td>
                                        <span class="dash-status dash-status-{{ in_array($s['status'], ['disetujui','menunggu_pembayaran','selesai','batal','tidak_hadir']) ? $s['status'] : 'default' }}">
                                            {{ $s['status_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
