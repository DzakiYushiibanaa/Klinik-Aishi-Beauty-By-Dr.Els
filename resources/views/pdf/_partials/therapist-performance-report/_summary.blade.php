<div class="section-title">Ringkasan</div>
<table class="summary">
    <tr>
        <td class="label">Total Terapis Aktif</td>
        <td class="value">{{ number_format($summary['total_active'], 0, ',', '.') }} orang</td>
        <td class="label">Treatment Ditangani</td>
        <td class="value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} sesi</td>
    </tr>
    <tr>
        <td class="label">Total Pendapatan</td>
        <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
        <td class="label">Total Komisi</td>
        <td class="value emerald">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Terapis Terproduktif</td>
        <td colspan="3" class="value">
            {{ $summary['best_therapist']['name'] ?? '—' }}
            @if($summary['best_therapist'])
                <span style="color:#888; font-weight:normal;">({{ number_format($summary['best_therapist']['count'], 0, ',', '.') }} sesi)</span>
            @endif
        </td>
    </tr>
</table>
