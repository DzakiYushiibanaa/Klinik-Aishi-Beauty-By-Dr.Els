<div class="section-title">Ringkasan</div>
<table class="summary">
    <tr>
        <td class="label">Total Dokter Aktif</td>
        <td class="value">{{ number_format($summary['total_active'], 0, ',', '.') }} orang</td>
        <td class="label">Total Konsultasi</td>
        <td class="value">{{ number_format($summary['total_konsultasi'], 0, ',', '.') }} sesi</td>
    </tr>
    <tr>
        <td class="label">Treatment Ditangani</td>
        <td class="value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} sesi</td>
        <td class="label">Total Pendapatan</td>
        <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Total Komisi</td>
        <td class="value emerald">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</td>
        <td class="label">Dokter Terproduktif</td>
        <td class="value">
            {{ $summary['best_doctor']['name'] ?? '—' }}
            @if($summary['best_doctor'])
                <span style="color:#888; font-weight:normal;">({{ $summary['best_doctor']['count'] }} layanan)</span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="label">Rate Komisi</td>
        <td colspan="3" class="value" style="color:#475569;">Konsultasi 15% • Treatment 10%</td>
    </tr>
</table>
