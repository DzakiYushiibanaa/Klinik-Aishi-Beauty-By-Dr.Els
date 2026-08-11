<div class="section-title">Detail Kinerja Terapis</div>
<table class="detail">
    <thead>
        <tr>
            <th style="width:25px;">No</th>
            <th>Nama Terapis</th>
            <th class="text-center">Treatment</th>
            <th class="text-center">Pasien</th>
            <th>Treatment Terfavorit</th>
            <th class="text-right">Pendapatan</th>
            <th class="text-center">Komisi%</th>
            <th class="text-right">Total Komisi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $r['terapis_name'] }}</strong><br>
                    <span style="color:#888;font-size:8px;">{{ $r['kode_terapis'] }}</span>
                </td>
                <td class="text-center">{{ number_format($r['total_treatment'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($r['total_pasien'], 0, ',', '.') }}</td>
                <td>{{ $r['favorite_treatment'] ?? '—' }}</td>
                <td class="text-right gold">Rp {{ number_format($r['total_revenue'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $r['commission_rate'] }}%</td>
                <td class="text-right emerald">Rp {{ number_format($r['total_commission'], 0, ',', '.') }}</td>
                <td>{{ ucfirst($r['status_kerja']) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:20px; color:#888;">
                    Belum ada data kinerja terapis pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if($rows->count() > 0)
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format($tableTotals['total_treatment'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($tableTotals['total_pasien'], 0, ',', '.') }}</td>
                <td>—</td>
                <td class="text-right gold">Rp {{ number_format($tableTotals['total_revenue'], 0, ',', '.') }}</td>
                <td>—</td>
                <td class="text-right emerald">Rp {{ number_format($tableTotals['total_commission'], 0, ',', '.') }}</td>
                <td>{{ $tableTotals['therapist_count'] }} terapis</td>
            </tr>
        </tfoot>
    @endif
</table>
